<?php
    final class edit extends AbstractPicoPlugin
    {
        public function onRequestUrl(&$url)
        {
            if ($url != "edit") {
                return;
            } ?>
            <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit</title>
</head>
            Файл: <?php echo $_GET["file"]; ?>
            <?php

            $filepath = $this->getPico()->getConfig('content_dir') . $_GET["file"] . ".md";

            $recoverypath = $this->getPico()->getConfig('content_dir')
            . "../recovery/" . $_GET["file"] . "/" . date("d-m-Y") .".md";

            if (isset($_POST['text']) && $_POST['password'] == "1122") {
                if (file_exists($filepath)) {
                    $ttext = file_get_contents($filepath);
                }

                if ($_POST['text'] != $ttext) {
                    $this->ensurePath($filepath);
                    $this->ensurePath($recoverypath);

                    file_put_contents($filepath, $_POST['text']);
                    file_put_contents($recoverypath, $_POST['text']);

                    $change = str_word_count($_POST['text']) - str_word_count($ttext);

                    $this->logmsg("Edited " . $_GET["file"] . (($change > 0) ? " +" : " ") . $change);

                    if ($_POST['text'] == "") {
                        unlink($filepath); ?>
                    <b>Файл удален</b>
                    <?php
                    } else {
                        ?>
                    <b>Успешно сохранено</b>
                    <?php
                    }
                } else {
                    ?><b>Файл не изменён</b><?php
                }
            } elseif (isset($_POST['password']) && $_POST['password'] !== "1122") {
                ?>
                <b>Неверный пароль</b>
                <?php
            }
            if (isset($_POST['text'])) {
                $text = $_POST['text'];
            } elseif (file_exists($filepath) && $ttext) {
                $text = $ttext;
            } elseif (file_exists($filepath)) {
                $text = file_get_contents($filepath);
            } else {
                $text = "";
            } ?>
            <style>
                form, textarea {
                    width: 100%;
                }
            </style>
                <form action="" method="post">
                    <textarea name="text" rows="20"><?php echo htmlspecialchars($text) ?></textarea>
                    <br>
                    Пароль: <input type="password" name="password" />
                    <br>
                    <input type="submit" value="Сохранить"/>
                    <input type="reset" />
                    <a href="/<?php echo $_GET["file"] ?>">Смотреть страницу</a>
                </form>
            <?php

            exit();
        }

        public function logmsg($message)
        {
            return;

            $filename = $this->getPico()->getConfig('content_dir') . "edits/log" . date("d-m-Y") . ".md";

            if (file_exists($filename)) {
                $logs = file_get_contents($filename);
            } else {
                $logs = "/*\nTitle: Логи изменений от " . date('d-m-Y') . "\n*/\nВремя указано в GMT";
            }

            $logs .= "\n\n[" . date("H:i") . "] " . $message;
            file_put_contents($filename, $logs);
        }

        public function ensurePath($path)
        {
            $fragments = explode("/", $path);
            $temppath = "";

            foreach ($fragments as $fragment) {
                if (strpos($fragment, ".md")) {
                    continue;
                }

                $temppath .= "/" . $fragment;
                if (!is_dir($temppath)) {
                    mkdir($temppath);
                }
            }
        }
    }
?>
