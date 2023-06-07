<?php 

/**
* @param string $method The HTTP method for this route
* @param string $uri The URI this route will match
* @param bool $isRegex (Optional) Flag for whether the URI is a regular expression
*/
#[Attribute(Attribute::TARGET_METHOD)]
class route
{
    const get = 1;
	const post = 2;
	const normal_all = self::get | self::post;

    const xhr_get = 4;
	const xhr_post = 8;
	const xhr_all = self::xhr_get | self::xhr_post;
    
	const all = self::normal_all | self::xhr_all;

    public function __construct(
        public int $method = self::get,
        public string $uri = "",
        public string $mime = "",
        public string $session = "",
        public string $otherwise = ""
    ){}
}
?>