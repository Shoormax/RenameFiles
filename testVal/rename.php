<?php
set_time_limit(-1);
setlocale (LC_ALL, "fr_FR");

$ajd =  new DateTime('now');
echo '<link rel="stylesheet" href="css.css">';
echo $ajd->format('Y-m-d h:i:s').'<br>';

require_once('../getid3/getid3.php');
require_once('../classes/Rename.php');

$extensions = array('.mp3', '.m4a', '.wav', '.mp4');
$dossierArtisteAlbum = array();
$musiques = array();
$getID3 = new getID3;
$separateur = 'separateur';
$dest = 'E:/zicIpod';

if(!file_exists($dest)) {
    mkdir($dest, 0777, true);
}

if(isset($_POST['chemin']) && !empty($_POST['chemin']) && file_exists($_POST['chemin'])) {
    $parents = array($_POST['chemin']);
}
else {
    die('Chemin non valide.');
}

for($i = 0; $i < 10; $i++) {
    foreach ($parents as $parent) {
        foreach (Rename::getSousDossiers($parent) as $sousDossier) {
            if(!in_array($sousDossier, $parents)) {
                $parents[] = $sousDossier;
            }
        }
    }
}

foreach($parents as $parent) {
    foreach(Rename::getMusiques($parent) as $filename) {
        $ThisFileInfo = $getID3->analyze($filename);
        $tempName = explode('.',$ThisFileInfo['filename'])[0];

        getid3_lib::CopyTagsToComments($ThisFileInfo);

        if(!empty($ThisFileInfo['fileformat'])) {
            $extension = '.'.$ThisFileInfo['fileformat'];
        }
        else {
            $temp = explode('.',$ThisFileInfo['filename']);
            $extension = '.'.array_pop($temp);
            if(!in_array($extension, $extensions)) {
                echo '<br>extensionwtf : '.$extension.'<br>';
            }
        }

        if(in_array($extension, $extensions)) {
            $oldFullName = $ThisFileInfo['filenamepath'];
            $titre_recup = empty($ThisFileInfo['comments']['title']) ? 'NoTitle'.$tempName : $ThisFileInfo['comments']['title'][0];

            $titre_recup = Rename::replaceInvalidChar($titre_recup);
            $titre = $parent.'/'.$titre_recup;
            $titre_duplicate = '';

            $i = 1;
            while(file_exists($titre.$titre_duplicate) || file_exists($titre.$titre_duplicate.$extension)) {
                $titre_duplicate = ' ('.$i.')';
                $i++;
            }
            $titre = $titre.$titre_duplicate.$extension;

            $album_recup = empty($ThisFileInfo['comments']['album']) ? 'NoAlbum' : $ThisFileInfo['comments']['album'][0];
            $artist_recup = empty($ThisFileInfo['comments']['artist']) ? 'NoArtist' : $ThisFileInfo['comments']['artist'][0];

            if(isset($_POST['showInfos']) && !empty($_POST['showInfos']) && $_POST['showInfos']) {
                echo '<table class="array">';
                echo '<tr><td>Titre duplicate</td><td>'.$titre.'</td></tr>';
                echo '<tr><td>Titre</td><td>'.$titre_recup.'</td></tr>';
                echo '<tr><td>Titre écriture</td><td>'.str_replace($parent.'/', '',$titre).$separateur.$oldFullName.'</td></tr>';
                echo '<tr><td>Artiste</td><td>'.$artist_recup.'</td></tr>';
                echo '<tr><td>Album</td><td>'.$album_recup.'</td></tr>';
                echo '<table>';
                echo '<hr>';
            }

            $dossierArtisteAlbum[$artist_recup][$album_recup][] = str_replace($parent.'/', '',$titre).$separateur.$oldFullName;
//            die('<pre>'.htmlentities(print_r($dossierArtisteAlbum, true), ENT_SUBSTITUTE).'</pre>'); //On shot
//            if(sizeof($dossierArtisteAlbum) > 50) {
//                die('<pre>'.htmlentities(print_r($dossierArtisteAlbum, true), ENT_SUBSTITUTE).'</pre>');
//                break 2;
//            }
//            break;
        }
        else {
            echo 'CAY UN DOSSIER : <pre>'.$filename.$extension.'</pre>';
        }
    }
}

foreach ($dossierArtisteAlbum as $i => $key) {
    $dossierACreer = Rename::replaceInvalidChar($i);
    foreach ($key as $j => $zics) {
        foreach ($zics as $zic) {
            $temp = explode($separateur, $zic);
            $titre = $temp[0];
            $oldFullName = $temp[1];
            $dossierChildrenACreer = Rename::replaceInvalidChar($j);

            if (!file_exists($dest.'/'.$dossierACreer . '/' . $dossierChildrenACreer)) {
                mkdir($dest.'/'.$dossierACreer . '/' . $dossierChildrenACreer, 0777, true);
            }

            if (!copy($oldFullName, $dest.'/'.$dossierACreer . '/' . $dossierChildrenACreer . '/' . $titre)) {
                echo '<br>Failure !<br>';
                var_dump($oldFullName);
                var_dump($dest.'/'.$dossierACreer . '/' . $dossierChildrenACreer . '/' . $titre);
            }
        }
    }
}
$ajd =  new DateTime('now');
echo $ajd->format('Y-m-d h:i:s').'<br>';
//die('<pre>'.htmlentities(print_r($dossierArtisteAlbum, true), ENT_SUBSTITUTE).'</pre>');
