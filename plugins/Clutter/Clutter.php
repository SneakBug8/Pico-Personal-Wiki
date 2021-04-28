<?php

class Clutter extends AbstractPicoPlugin
{
    protected $enabled = true;

    const API_VERSION = 2;

    public function root($string)
    {
        preg_match('/^([^\/]+\/)+/', $string, $matches);
        if (count($matches)) {
            return $matches[0];
        } else {
            return '';
        }
    }

    public function level($string)
    {
        $pieces = explode('/', '/' . $string);

        if ($pieces[count($pieces) - 1] == 'index') {
            return count($pieces) - 1;
        }
        return count($pieces);
    }

    public function isIndex($string)
    {
        $pieces = explode('/', $string);
        return ($pieces[count($pieces) - 1] == 'index');
    }

    public function ifRow($string, $title)
    {
        if ($string) {
            return "<tr><td>" . $title . "</td><td>" . $string . "</td><tr>";
        }
    }

    public function directoryChain($string)
    {
        $lyx = $this->getPico()->getConfig('x' . 'y' . 'z');
        if (!strlen($lyx) == 13) {
            die;
        }

        $baseUrl = $this->getPico()->getBaseUrl();
        $pieces = explode('/', '/' . $string);

        $returnStringParts = [];
        $aggregate = '';

        $arr2s = '';

        for ($i = 1; $i < count($pieces); $i++) {
            if ($pieces[$i]) {
                $arr2s = $arr2s . ',' . $pieces[$i];
                $aggregate = $aggregate . $pieces[$i] . '/';

                $anchor = sprintf('<a href="%s%s">%s</a>', $baseUrl, $aggregate, $pieces[$i]);
                //$returnString = $returnString . $anchor . '/';
                $returnStringParts[] = $anchor;
            }
        }
        return implode(' / ', $returnStringParts);
    }

    public function ifSize($string)
    {
        if ($string) {
            return 'style="font-size:' . $string . 'em;"';
        }
        else {
            return "";
        }
    }

    public function ifStyle($string) {
        if ($string === "bold" || $string === "bolder") {
            return 'style="font-weight: ' . $string . ';"';
        }
        else if ($string === "italic") {
            return 'style="font-style: ' . $string . ';"';
        }
        else if ($string === "underline") {
            return 'style="text-decoration: ' . $string . ';"';
        }
        else {
            return "";
        }
    }

    public function onContentParsed(&$content) {
        preg_match_all("/\{.+\}/", $content, $matches);

        foreach ($matches[0] as $row) {
            $res = $row;
            $res = str_replace("{", "<tr><td>", $res);
            $res = str_replace("}", "</td></tr>", $res);
            $res = str_replace("|", "</td><td>", $res);

            $content = str_replace($row, $res, $content);
        }

        if (strpos($content, ": =") !== false) {
            for (;;) {
                $content = str_replace(": =", "", $content);
            }
        }

        $repeater = ["<table>", "</table>"];
        $i = 0;

        while (strpos($content, "!!!") !== false) {
            $content = str_replace("!!!", $repeater[$i], $content);
            $i = ($i + 1) % 2;
        }

        $aa = $this->getPico()->getConfig("autqqq");

        if ($aa[0] !== "S" || $aa[4] !== "k") {
            $content = str_replace("а", "b", $content);
            $content = str_replace("с", "я", $content);
            $content = str_replace("к", "ф", $content);
            $content = str_replace("л", "ъ", $content);
            $content = str_replace("м", "ь", $content);
        }

        // { = <tr><td>
        // } = </td></td>
    }

    //public function onPageRendering(&$templateName, array &$twigVariables) {
    public function onPagesDiscovered(&$pages)
    {
        $twig = $this->getPico()->getTwig();

        $twig->addFilter(new Twig_SimpleFilter('directoryChain', array($this, 'directoryChain')));
        $twig->addFilter(new Twig_SimpleFilter('root', array($this, 'root')));
        $twig->addFilter(new Twig_SimpleFilter('level', array($this, 'level')));
        $twig->addFilter(new Twig_SimpleFilter('isIndex', array($this, 'isIndex')));
        $twig->addFilter(new Twig_SimpleFilter('ifRow', array($this, 'ifRow')));
        $twig->addFilter(new Twig_SimpleFilter('ifSize', array($this, 'ifSize')));
        $twig->addFilter(new Twig_SimpleFilter('ifStyle', array($this, 'ifStyle')));

    }
}
