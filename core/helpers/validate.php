<?php

function multiple_isset($source, $item): bool
{
    return !((is_array($source) && !isset($source[$item])) || (is_object($source) && !isset($source->$item)));
}

function validate(&$source, array $items)
{
    $errors = [];

    foreach ($items as $item => $rules) 
    {
        if (!multiple_isset($source, $item))
            return lang("validation.input.missing", $rules['name']);

        $value = null;
        if (is_array($source))
            $value = $source[$item];
        else
            $value = $source->$item;


        if (!isset($rules["required"]) && empty($value))
            continue;

        foreach ($rules as $rule => $rule_value) {
            switch ($rule) {
                case 'required':

                    if (!is_numeric($value) && empty($value)) {
                        $errors[] = lang("validation.required_error", $rules['name']);
                        break 2; // 1: exit switch 2: exit foreach
                    }

                    break;
                case 'min':

                    if(!isset($rules['numeric']))
                    {
                        if (mb_strlen($value, 'UTF-8') < $rule_value) {
                            $errors[] = lang("validation.min_len_error", $rules['name'], $rule_value);
                            break 2;
                        }
                    }
                    else
                    {
                        $value = isset($rules["money"]) ? to_float($value) : $value;
                        $ruleValue = is_array($rule_value) ? $rule_value["value"] : $rule_value;
    
                        if ($value < $ruleValue) {
                            if (isset($rule_value["lang"]))
                                $errors[] = lang($rule_value["lang"], $rule_value["value"], $value);
                            else
                                $errors[] = lang("validation.numeric.value.error", $rules['name'], $value, $rule_value, lang("small"));
                        }
                    }
                    
                    break;
                case 'max':

                    if(!isset($rules['numeric']))
                    {
                        if (mb_strlen($value, 'UTF-8') > $rule_value)
                            $errors[] = lang("validation.max_len_error", $rules['name'], $rule_value);
                    }
                    else
                    {
                        $value = isset($rules["money"]) ? to_float($value) : $value;
                        $ruleValue = is_array($rule_value) ? $rule_value["value"] : $rule_value;
    
                        if ($value > $ruleValue) {
                            if (isset($rule_value["lang"]))
                                $errors[] = lang($rule_value["lang"], $rule_value["value"]);
                            else
                                $errors[] = lang("validation.numeric.value.error", $value, $rule_value, lang("big"));
                        }
                    }
                    
                    break;

                case "is":

                    if ($value != $rule_value)
                        $errors[] = lang("validation.checked_error", $rules["name"]);

                    break;

                case 'match':

                    if (!multiple_isset($source, $rule_value))
                        return lang("validation.input.missing", $items[$rule_value]['name']);

                    $matchValue = is_object($source) ? $source->$rule_value : $source[$rule_value];
                    if ($value != $matchValue)
                        $errors[] = lang("validation.matches_error", $rules['name'], $items[$rule_value]['name']);

                    #unlink($rvalue);

                    break;

                case 'no-match':

                    if (!multiple_isset($source, $rule_value))
                        return lang("validation.input.missing", $items[$rule_value]['name']);

                    $nmatchValue = is_object($source) ? $source->$rule_value : $source[$rule_value];
                    if ($value == $nmatchValue)
                        $errors[] = lang("validation.notmatches_error", $rules['name'], $items[$rule_value]['name']);

                    break;

                case 'email':

                    if (!validate_email($value))
                        $errors[] = lang("validation.email_error");

                    break;

                case 'money':

                    if (to_float($value) == "NaN") {
                        $errors[] = lang("validation.money.error", $rules["name"]);
                        break 2;
                    }


                    break;

                case 'numeric':

                    if (!is_numeric($value)) {
                        $errors[] = lang("validation.numeric_error", $rules["name"]);
                        break 2;
                    }

                    break;

                case 'strnum':

                    if (!is_numeric($value)) {
                        $errors[] = lang("validation.numeric_error", $rules["name"]);
                        break 2;
                    }

                    break;

                case "url":
                    
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL))
                        $errors[] = lang("validation.url.error", $rules["name"]);

                    break;

                case "letter-space": # harf & bosluk

                    if (!letter_space($value))
                        $errors[] = lang("validation.letter.space.error", $rules["name"]);

                    break;
                case "letter-dash": # harf 

                    if (!letter_dash($value))
                        $errors[] = lang("validation.letter.dash.error", $rules["name"]);

                    break;
                case "dash": # harf & sayı

                    if (!alpha_dash($value))
                        $errors[] = lang("validation.alpha.dash.error", $rules["name"]);

                    break;
                case "space": # harf & sayı & boşluk

                    if (!alpha_space($value))
                        $errors[] = lang("validation.alpha.space.error", $rules["name"]);

                    break;
                case "phone":

                    if (!validate_phone($value, $rule_value))
                        $errors[] = lang("validation.phone.error", $rules["name"]);

                    break;

                case "ip":
                    if (!validate_ip($value))
                        $errors[] = lang("validation.ip.error", $rules["name"]);
                    break;

                case "clear":

                    if (is_object($source))
                        $source->$item = htmlentities($source->$item, ENT_QUOTES | ENT_HTML5);
                    else
                        $source[$item] = htmlentities($source[$item], ENT_QUOTES | ENT_HTML5);

                    break;
            }
        }
    }

    if (empty($errors))
        return null;

    return join('<br>', $errors);
}

/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

function letter_space($data)
{
    return (!preg_match("/^([a-zA-ZÇçĞğıİÖöŞşÜü ])+$/i", $data)) ? false : true;
}

function letter_dash($data)
{
    return (!preg_match("/^([a-zA-ZÇçĞğıİÖöŞşÜü])+$/i", $data)) ? false : true;
}

function alpha_dash($data)
{
    return (!preg_match("/^([a-zA-Z0-9,.@_-])+$/i", $data)) ? false : true;
}

function alpha_space($data)
{
    return (!preg_match("/^([A-Za-zÇçĞğıİÖöŞşÜü0-9-_, ])+$/i", $data)) ? false : true;
}

function alpha_space2($data)
{
    return (!preg_match("/^([A-Za-zÇçĞğıİÖöŞşÜü0-9-_&=+% ])+$/i", $data)) ? false : true;
}

function validate_phone($value, $country)
{
    #turkcell & vodefone & avea 
    #TODO: need global

    #if($country == "tr")
    return strlen(preg_replace('/\(5(0|1|2|3|4|5)\d\)[- ]\d{3}[- ]\d{2}[- ]\d{2}/s', '', $value, 1)) == 0;
}

function validate_email($email)
{
    $domain = explode('@', $email);
    $domain = array_pop($domain);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return false;

    if (preg_match('/^([a-z0-9]+([_\.\-]{1}[a-z0-9]+)*){1}([@]){1}([a-z0-9]+([_\-]{1}[a-z0-9]+)*)+(([\.]{1}[a-z]{2,6}){0,3}){1}$/i', $email) === 0)
        return false;

    # internetsiz çalışırken sıkıntı
    #if ( !(checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) ) 
    # return false;

    return true;
}
