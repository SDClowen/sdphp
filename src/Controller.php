<?php
namespace Core;

use Latte\Engine as LatteEngine;

class Controller
{
    /*
     *  Ran method attributes 
     */
    public $attributes;

    /*
     *  The database instance
     */
    public Database $db;

    /*
     * The system config
     */
    public $config;

    /*
     * Create new instance of this class
     */
    public static function new() : Controller
    {
        return new self();
    }

    /**
     * Render a view file
     *
     * @param string $view The view file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return void 
     */
    public function render(string $view, array $args = [], bool $isEcho = true)
    {
        global $appConfig;

        if (! file_exists($file = VDIR . "/" . strtolower($view) . ".php"))
            exit("Could not found the view file: $view");

        if ($appConfig->latte) {
            $latte = new LatteEngine;
            $latte->setTempDirectory(APP_DIR . "/caches");
            $latte->setAutoRefresh(strtolower($appConfig->mode) == "development");

            if($isEcho)
                $latte->render($file, $args);
            else
                return $latte->renderToString($file, $args);
        } else {
            extract($args, EXTR_SKIP);
            ob_start();

            if (is_readable($file))
                require $file;
            else
                exit("Could not found the view file: $view");

            $result = ob_get_clean();

            if (!$isEcho)
                return $result;

            echo $result;
        }
    }

    public function view(string $normalView, string $xhrView, string $title, array $data = [])
    {
        $data["title"] = $title;
        $data["content"] = $this->render($xhrView, $data, false);

        if (Request::isAjax())
            die(data_json($data));

        $this->render($normalView, $data);
    }
}
?>
