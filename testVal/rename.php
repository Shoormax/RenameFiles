<?php
set_time_limit(-1);
$ajd =  new DateTime('now');
echo $ajd->format('Y-m-d h:i:s').'<br>';
require_once('../getid3/getid3.php');
require_once('../classes/Rename.php');

$extensions = array('.mp3', '.m4a', '.wav');
$dossierArtisteAlbum = array();
// Initialize getID3 engine
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

$j = 0;
foreach($parents as $parent) {
    //echo $parent;
    foreach(Rename::getMusiques($parent) as $filename) {
        $j++;
        $ThisFileInfo = $getID3->analyze($filename);

        getid3_lib::CopyTagsToComments($ThisFileInfo);
        $extension = '.'.$ThisFileInfo['fileformat'];

        if(in_array($extension, $extensions)) {
            $oldFullName = $ThisFileInfo['filenamepath'];
            $titre_recup = empty($ThisFileInfo['comments']['title']) ? 'NoTitle' : $ThisFileInfo['comments']['title'][0];
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

            echo 'Titre duplicate : '.$titre.'<br>';
            $dossierArtisteAlbum[$artist_recup][$album_recup][] = str_replace($parent.'/', '',$titre);
            echo 'Titre : ';var_dump($album_recup);
            echo 'Artiste : ';var_dump($artist_recup);
            echo 'Album : ';var_dump($titre_recup);
            echo '<hr>';
//            if($j == 50) {
                foreach ($dossierArtisteAlbum as $i => $key) {
                    //@todo a améliorer de ouf
                    $dossierACreer = str_replace('/', ' - ', $i);
                    $dossierACreer = str_replace(':', '', $dossierACreer);
                    $dossierACreer = str_replace('\\', '', $dossierACreer);
                    $dossierACreer = str_replace('*', '', $dossierACreer);
                    $dossierACreer = str_replace('<', '', $dossierACreer);
                    $dossierACreer = str_replace('>', '', $dossierACreer);
                    $dossierACreer = str_replace('|', '', $dossierACreer);
                    $dossierACreer = str_replace('?', '', $dossierACreer);

                    foreach ($key as $j => $zic) {
                        $dossierChildrenACreer = str_replace('/', ' - ', $j);
                        $dossierChildrenACreer = str_replace(':', '', $dossierChildrenACreer);
                        $dossierChildrenACreer = str_replace('\\', '', $dossierChildrenACreer);
                        $dossierChildrenACreer = str_replace('*', '', $dossierChildrenACreer);
                        $dossierChildrenACreer = str_replace('<', '', $dossierChildrenACreer);
                        $dossierChildrenACreer = str_replace('>', '', $dossierChildrenACreer);
                        $dossierChildrenACreer = str_replace('|', '', $dossierChildrenACreer);
                        $dossierChildrenACreer = str_replace('?', '', $dossierChildrenACreer);

                        if(!file_exists($dossierACreer)){
//                            echo 'Dossier a creer : '.$dossierACreer.'/'.$dossierChildrenACreer.'<br>';
//                            echo 'Dossier a children creer : '.$dossierACreer.'/'.$dossierChildrenACreer.'/'. str_replace($parent.'/', '',$titre).'<br>';
//                            echo 'Fichier de base : '.$oldFullName.'<br>';
//                            echo '<br><hr><br>';
//                            sleep(1);
//                            echo 'mkdir : '.$dossierACreer.'/'.$dossierChildrenACreer;

                            mkdir($dossierACreer.'/'.$dossierChildrenACreer, 0777, true);
                            try {
//                                var_dump($oldFullName);
//                                var_dump($dossierACreer.'/'.$dossierChildrenACreer.'/'. str_replace($parent.'/', '',$titre));
                                copy($oldFullName, $dossierACreer.'/'.$dossierChildrenACreer.'/'. str_replace($parent.'/', '',$titre));
                            }catch (Exception $e) {
                                var_dump($oldFullName);
                                var_dump($dossierACreer.'/'.$dossierChildrenACreer.'/'. str_replace($parent.'/', '',$titre));
                                var_dump($e->getMessage());
                            }
                        }
                    }
//                }
//                die('<pre>'.htmlentities(print_r($dossierArtisteAlbum, true), ENT_SUBSTITUTE).'</pre>');
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


