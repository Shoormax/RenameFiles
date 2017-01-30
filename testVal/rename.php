<?php
set_time_limit(-1);
$ajd =  new DateTime('now');
echo '<link rel="stylesheet" href="css.css">';
echo $ajd->format('Y-m-d h:i:s').'<br>';
require_once('../getid3/getid3.php');
require_once('../classes/Rename.php');

$extensions = array('.mp3', '.m4a', '.wav', '.mp4');
$dossierArtisteAlbum = array();
$getID3 = new getID3;

if(isset($_POST['chemin']) && !empty($_POST['chemin']) && file_exists($_POST['chemin'])) {
    $parents = array($_POST['chemin']);
}
else {
    die('Chemin non valide');
}

$musiques = array();
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
            $titre_recup = empty($ThisFileInfo['comments']['title']) ? 'NoTitle' : $ThisFileInfo['comments']['title'][0];
            $titre_recup = Rename::replaceInvalidChar($titre_recup);
            $titre = $parent.'/'.$titre_recup.$extension;
            $titre_duplicate = '';

            $i = 1;
            while(file_exists($titre.$titre_duplicate)) {
                $titre_duplicate .= ' ('.$i.')';
                $i++;
            }

            $titre = $titre.$titre_duplicate;

            $album_recup = empty($ThisFileInfo['comments']['album']) ? 'NoAlbum' : $ThisFileInfo['comments']['album'][0];
            $artist_recup = empty($ThisFileInfo['comments']['artist']) ? 'NoArtist' : $ThisFileInfo['comments']['artist'][0];

            echo '<table class="array">';
            echo '<tr><td>Titre duplicate</td><td>'.$titre.'</td></tr>';
            $dossierArtisteAlbum[$artist_recup][$album_recup][] = str_replace($parent.'/', '',$titre);
            echo '<tr><td>Titre</td><td>'. $titre_recup.'</td></tr>';
            echo '<tr><td>Artiste</td><td>'.$artist_recup.'</td></tr>';
            echo '<tr><td>Album</td><td>'.$album_recup.'</td></tr>';
            echo '<table>';
            echo '<hr>';
            
            //A sortir du grand foreach
            foreach ($dossierArtisteAlbum as $i => $key) {
                $dossierACreer = Rename::replaceInvalidChar($i);

                foreach ($key as $j => $zic) {
                    $dossierChildrenACreer = Rename::replaceInvalidChar($j);
                    if(!file_exists($dossierACreer . '/' . $dossierChildrenACreer)) {
                        mkdir($dossierACreer . '/' . $dossierChildrenACreer, 0777, true);
                    }
                    try {
                        if(copy($oldFullName, $dossierACreer.'/'.$dossierChildrenACreer.'/'. str_replace($parent.'/', '',$titre))) {
                            echo '<br>Success !<br>';
                        }
                        else {
                            echo '<br>Failure !<br>';
                            var_dump($oldFullName);
                            var_dump($dossierACreer.'/'.$dossierChildrenACreer.'/'. str_replace($parent.'/', '',$titre));
                        }
                    }
                    catch (Exception $e) {
                        var_dump($oldFullName);
                        var_dump($dossierACreer.'/'.$dossierChildrenACreer.'/'. str_replace($parent.'/', '',$titre));
                        var_dump($e->getMessage());
                    }
                }
//                die; //pour tester avec un seul fichier, décomenter cette ligne
            }
        }
        else {
            echo 'CAY UN DOSSIER : <pre>'.$filename.$extension.'</pre>';
        }
    }
}
$ajd =  new DateTime('now');
echo $ajd->format('Y-m-d h:i:s').'<br>';
//die('<pre>'.htmlentities(print_r($dossierArtisteAlbum, true), ENT_SUBSTITUTE).'</pre>');
