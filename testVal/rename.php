<?php
set_time_limit(-1);
setlocale (LC_ALL, "fr_FR");

$ajd =  new DateTime('now');
echo '<link rel="stylesheet" href="css.css">';
echo $ajd->format('Y-m-d h:i:s').'<br>';

require_once('../getid3/getid3.php');
require_once('../classes/Rename.php');

$extensions = array('.mp3', '.m4a', '.wav', '.mp4', '.flac');
$regexExtensions = str_replace('.', '\\.', '/'.implode('|', $extensions).'/');

$dossierArtisteAlbum = array();
$musiques = array();
$id3 = new getID3;
$separateur = 'separateur';
$dest = isset($_POST['dest']) && !empty($_POST['dest']) ? $_POST['dest'] : '';

if(!file_exists($dest)) {
    mkdir($dest, 0777, true);
}

if(!is_dir($dest)) {
    die('Destination éronnée.');
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
        $thisFileInfo = $id3->analyze($filename);
        $tempName = explode('.',$thisFileInfo['filename'])[0];

        getid3_lib::CopyTagsToComments($thisFileInfo);

        if(!empty($thisFileInfo['fileformat'])) {
            $extension = '.'.$thisFileInfo['fileformat'];
        }
        else {
            $temp = explode('.',$thisFileInfo['filename']);
            $extension = '.'.array_pop($temp);
            if(!in_array($extension, $extensions)) {
                echo '<br>extensionwtf : '.$extension.'<br>';
            }
        }

        if(in_array($extension, $extensions)) {
            $oldFullName = $thisFileInfo['filenamepath'];
            $titre_recup = empty($thisFileInfo['comments']['title']) ? 'NoTitle'.$tempName : $thisFileInfo['comments']['title'][0];

            $titre_recup = Rename::replaceInvalidChar($titre_recup);
            $titre = $parent.'/'.$titre_recup;

            $titre = $titre.$extension;

            $album_recup = empty($thisFileInfo['comments']['album']) ? 'NoAlbum' : $thisFileInfo['comments']['album'][0];
            $artist_recup = empty($thisFileInfo['comments']['artist']) ? 'NoArtist' : $thisFileInfo['comments']['artist'][0];

            if(isset($_POST['showInfos']) && !empty($_POST['showInfos'])) {
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

            $extension = '';

            preg_match_all($regexExtensions, $titre, $matches);
            $titre = preg_replace($regexExtensions,'', $titre);

            $i = 1;
            $titreDuplicate = '';

            while(file_exists($dest.'/'.$dossierACreer . '/' . $dossierChildrenACreer . '/' . $titre.$titreDuplicate.$matches[0][0])) {
                $titreDuplicate = ' ('.$i.')';
                $i++;
            }

            $fullFile = $dest.'/'.$dossierACreer . '/' . $dossierChildrenACreer . '/' . $titre.$titreDuplicate.$matches[0][0];

            if (!copy($oldFullName, $fullFile)) {
                echo '<br>Failure !<br>';
                var_dump($oldFullName);
                var_dump($fullFile);
            }
        }
    }
}
$ajd =  new DateTime('now');
echo $ajd->format('Y-m-d h:i:s').'<br>';
