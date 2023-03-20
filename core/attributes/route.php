<?php 

/**
* @param string $method The HTTP method for this route
* @param string $uri The URI this route will match
* @param bool $isRegex (Optional) Flag for whether the URI is a regular expression
*/
#[Attribute(Attribute::TARGET_METHOD)]
class route{
    public function __construct(
        public bool $xhr = false,
        public string $method = "GET",
        public string $uri = "",
        public string $mime = "",
        public string $session = "",
        public string $otherwise = ""
    ){}
}
?>