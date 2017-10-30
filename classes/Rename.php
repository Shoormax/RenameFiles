<?php

class Rename
{
    public static $extensions = array('mp3', 'm4a', 'wav', 'mp4');
    public static $forbidenExtensions = array('php', 'txt', 'jpg', 'png', 'JPEG', 'jpeg', 'tiff', 'docx', 'html', 'db');
    public static $dossiersCaches = array('.', '..', '.idea', '. .php___jb_tmp___', 'SDA', 'classes');
    public static $chiffre = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    /**
     * Permet de renommer les noms des fichiers passés en paramètres
     * en récupérant le nom dans les propriétés du fichier
     *
     * @param array $files
     */
    public static function renameFiles($files)
    {
        foreach ($files as $file) {
            $chemin = self::decomposeFichier($file)['chemin'];
            $t = file_get_contents($file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            $tempnum = explode('ID', $t);
            $tempnum2 = explode('TIT2', $tempnum[1]);

            $number = '';

            for ($i = 0; $i < strlen($tempnum2[0]); $i++) {
                if (in_array($tempnum2[0][$i], self::$chiffre)) {
                    $number .= $tempnum2[0][$i];
                }
            }

            $number = strlen($number) == 1 ? '0' . $number . '.' : $number . '.';

            $temp = explode('TIT2', $t);

            $tempTitle = '';

            $temp2 = explode('TPE1', $temp[1]);

            for ($j = 0; $j < strlen($temp2[0]); $j++) {
                if (ctype_alpha($temp2[0][$j]) || $temp2[0][$j] === " ") {
                    $tempTitle .= $temp2[0][$j];
                }
            }

            $title = $number . ' ' . $tempTitle;

            rename($file, $chemin . $title . '.' . $extension);
        }
    }

    /**
     * Permet de récupérer le nom et le chemin du fichier
     *
     * @param string $file
     *
     * @return array
     */
    public static function decomposeFichier($file)
    {
        $array = explode('/', $file);
        $nom = array_pop($array);

        $chemin = '';
        foreach ($array as $ar) {
            $chemin .= $ar . '/';
        }

        return array(
            'nom' => $nom,
            'chemin' => $chemin
        );
    }

    /**
     * @param $files
     * @param string $parent
     *
     * @return array|bool
     */
    public static function orderFilesByType($files, $parent)
    {
        $musiques = array();
        $dossiers = array();

        if ($parent != '') {
            $parent = $parent . '/';
        }

        foreach ($files as $file) {
            $t = (explode('.', $file));
            if (!in_array($file, self::$dossiersCaches)) {
                if (in_array(array_pop($t), self::$extensions)) {
                    $musiques[] = $parent . $file;
                } else {
                    $dossiers[] = $parent . $file;
                }
            }
        }
        if (empty($musiques) && empty($dossiers)) {
            return false;
        }
        return array(
            'musiques' => $musiques,
            'dossiers' => $dossiers
        );
    }

    public static function getSousDossiers($parent)
    {
        $dossiers = array();
        if ($parent != './') {
            $parent = $parent . '/';
        }
        foreach (scandir($parent) as $fichier) {
            if (strlen($fichier) > 2) {
                if ($fichier[0] . $fichier[1] != '._' &&
                    !in_array($fichier, self::$dossiersCaches) &&
                    !in_array(pathinfo($fichier, PATHINFO_EXTENSION), self::$extensions) &&
                    !in_array(pathinfo($fichier, PATHINFO_EXTENSION), self::$forbidenExtensions)
                ) {
                    $dossiers[] = $parent . $fichier;
                }
            }
        }
        return $dossiers;
    }

    public static function getMusiques($parent)
    {
        $musiques = array();
        if ($parent != './') {
            $parent = $parent . '/';
        }
        foreach (scandir($parent) as $fichier) {
            if (!in_array($fichier, self::$dossiersCaches) &&
                in_array(pathinfo($fichier, PATHINFO_EXTENSION), self::$extensions) &&
                !in_array(pathinfo($fichier, PATHINFO_EXTENSION), self::$forbidenExtensions)
            ) {
                $musiques[] = $parent . $fichier;
            }
        }

        return $musiques;
    }

    public static function getRealTitle($file)
    {
        $props = file_get_contents($file);
        $tempnum2 = explode('TIT2', $props)[1];
        if ($tempnum2 != 'error') {
            $properties = array();

            foreach (explode($tempnum2[0], $tempnum2) as $property) {

                if (!empty($property) && self::isValidChar($property[0])) {
                    $properties[] = $property;

                }
            }

        }
    }

    public static function isValidChar($char)
    {
        return (ctype_alpha($char) || in_array($char, self::$chiffre));
    }

    public static function replaceInvalidChar($str)
    {
        $str = str_replace('/', ' - ', $str);
        $str = str_replace(':', '', $str);
        $str = str_replace('\\', '', $str);
        $str = str_replace('*', '', $str);
        $str = str_replace('<', '', $str);
        $str = str_replace('>', '', $str);
        $str = str_replace('|', '', $str);
        $str = str_replace('?', '', $str);
        $str = str_replace('"', '', $str);
        $str = str_replace('ç', 'c', $str);
        $str = str_replace('à', 'a', $str);
        $str = str_replace('â', 'a', $str);
        $str = str_replace('ä', 'a', $str);
        $str = str_replace('é', 'e', $str);
        $str = str_replace('ë', 'e', $str);
        $str = str_replace('è', 'e', $str);
        $str = str_replace('ê', 'e', $str);
        $str = str_replace('ô', 'o', $str);
        $str = str_replace('ö', 'o', $str);
        $str = str_replace('ø', 'o', $str);
        $str = str_replace('ç', 'c', $str);
        $str = str_replace('è', 'e', $str);
        $str = str_replace('ñ', 'n', $str);
        $str = str_replace('û', 'u', $str);
        $str = str_replace('ü', 'u', $str);
        $str = str_replace('ù', 'u', $str);
        $str = str_replace('&amp;', '&', $str);

        return trim($str);
    }

}