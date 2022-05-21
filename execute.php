<?php
function conv_date ($data,$inverti=false)
{
  if($inverti)
  {
  list ($g, $m, $y) = explode ("-", $data);
  return "$y-$m-$g";
  }
  else
  {
  list ($y, $m, $d) = explode ("-", $data);
  return "$d-$m-$y";
  }
}

function se_iscritto ($chatid,$tpreturn='NOME')
{
	$chatid = trim($chatid);
	$urlCheck = 'https://www.augusto.puglia.it/api/checkreg.php?par='.$chatid;
	$json = file_get_contents($urlCheck);
	$obj = json_decode($json,true);
	$array = $obj[1];
	$cont = $obj[0]["conteggio"];
	$array2 = $array["name_array"];
	$data = $array2[0];
	$nome = $data["nome"];
	$cognome = $data["cognome"];
	
	if($cont == 0)
		return 'NO';
	else
	{
		if($tpreturn == 'NOMECOGNOME')
			return $nome.' '.$cognome;
		if($tpreturn == 'NOME')
			return $nome;
		if($tpreturn == 'COGNOME')
			return $cognome;
	}
	
}

function trova_parola($parola,$descrizione){ // Parola: la parola da cercare | Descrizione: frase in cui cercare
	$descrizione = preg_replace("/\W/", " ", $descrizione); // elimino caratteri speciali
	$des_cerca=explode(" ",$descrizione); // esplodo le singole parolo
	$risultato = count($des_cerca); // conto il totale delle parole esplose
	@$_ritono = false;
	for($i=0; $i<=$risultato; $i++){ // ciclo per fare controllo
		if(@$des_cerca[$i]==@$parola){
			@$_ritono = true; // se la trovo chiudo ciclo e ritorno l'ok
			break;
		}		
	}	
	return $_ritono;
}



$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update)
{
  exit;
}

header("Content-Type: application/json");

$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$senderId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$domanda = isset($message['text']) ? $message['text'] : "";

$domanda = trim($domanda);
$domandaL = trim(strtolower($domanda));
//$risposta = strtolower($domanda);
$risposta = trim('I tecnici sono a lavoro per migliorarmi in modo da farmi rispondere prima e più efficacemente alle tue domande o comandi, dovrai avere pazienza se ancora non capisco tutto quello che mi chiedi. Ti posso fornire la lista dei comandi se mi chiedi "aiuto"');


$listacomandi = '/aiuto - lista dei comandi 
/iscrizione - RISERVATO AI VOLONTARI, insieme alla matricola del tuo coordinamento da accesso a dei servizi riservati ai volontari';

/*$contattiAss = 'Email: info@protezionecivilecasarano.org
Telefono: 08331855789
Fax: 08331850434
Cellulare: 3473912735
PEC: protezionecivilecasarano@pec.it

Puoi contattarci scrivendoci anche da qui. 

Scrivi: /scrivi testodelmessaggio. 

Esempio:
/scrivi ciao, vi segnalo un problema di rilevanza di protezione civile in via Bari. Un saluto da Mario Rossi';


$istr_allerameteo = 'Ecco come puoi usare il comando /allertameteo

/allertameteo ultima - Ti permettere di leggere le informazioni dell\'allerta corrente o appena passata.
/allertameteo iscrizione - Ci permette di avvisarti quando un\'allerta viene diramata.
/allertameteo disiscrizione - Ti permette di non ricevere più avvisi riguardanti eventuali allerte meteo.
/allertameteo info - Ti fa leggere di nuovo questo elenco.';*/

$lat = 0;
$lon = 0;

date_default_timezone_set('UTC+2');


//---- STAMPA LISTA COMANDI
if($domandaL == 'aiuto' or $domandaL == '/aiuto' or $domandaL == 'aiutami' or $domandaL == 'help' or $domandaL == '/help')
	$risposta = trim($listacomandi);

//---- STAMPA ORARIO
if($domandaL == 'che ore sono?' or $domandaL == 'mi dici l\'orario?' or $domandaL == 'sai dirmi l\'orario?' or $domandaL == 'ore?' or $domandaL == 'mi dici l\'ora?' or $domandaL == 'sai dirmi l\'ora?')
	$risposta = trim(date("H:i:s"));

//---- STAMPA CONTATTI
/*if($domandaL == 'contatti' or $domandaL == '/contatti')
	$risposta = trim($contattiAss);*/

//---- KEY ISCRIZIONE
if(substr($domandaL,0,10) == 'iscrizione' or substr($domandaL,0,11) == '/iscrizione')
{
	if(substr($domandaL,0,1) == '/')
		$codsocio = substr($domandaL,12);
	else
		$codsocio = substr($domandaL,11);
	
	$codsocio = trim($codsocio);
	$urlUser = 'https://www.augusto.puglia.it/api/readuser.php?c='.$codsocio;
	$json = file_get_contents($urlUser);
	$obj = json_decode($json,true);
	$array = $obj[1];
	$array2 = $array["name_array"];
	$data = $array2[0];
	$nome = $data["nome"];
	$cognome = $data["cognome"];
	
	if($nome == '' or $cognome == '')
		$risposta = trim('Mi dispiace non ti ho riconosciuto. Ho letto bene il tuo codice Telegram? Mi risulta >>'.$codsocio.'<<');
	else
	{
		$risposta = trim('Ciao, ti ho riconosciuto, sei proprio '.$nome.' '.$cognome.'! D\'ora in poi saprò come chiamarti quando mi servirai.
		
		Il tuo codice telegram è '.$chatId).'. Usalo per comunicarlo all\'assistenza tecnica in caso di bisogno.';
		$urlUserAppr = 'https://www.augusto.puglia.it/api/reguser.php?c='.$codsocio.'&chatid='.$chatId;
		$json = file_get_contents($urlUserAppr);
	}
	
	if($codsocio == '')
		$risposta = trim('Non posso riconoscerti se non mi fornisci il tuo codice Telegram visibile sul tuo profilo in AUGUSTO');
}


//---- KEY CIAO
if($domandaL=='ciao' or $domandaL=='salve' 
or $domandaL=='buongiorno' or $domandaL=='buon giorno'
or $domandaL=='buonasera' or $domandaL=='buona sera'
or $domandaL=='buonpomeriggio' or $domandaL=='buon pomeriggio')
{
	$risposta = "Ciao, Come posso esserti utile?";	
}

//---- KEY SERVIZI

if($domandaL=='servizi')
{
	//$risposta = "servizi";	
	//$urlUserAppr = 'https://www.augusto.puglia.it/api/comand.php?servizi=1&chatid='.$chatId;
		//$json = file_get_contents($urlUserAppr);
	$risposta = "Ciao! Servizi, Come posso esserti utile?";	
}


$parameters = array('chat_id' => $chatId, "text" => $risposta);
$parameters["method"] = "sendMessage";
echo json_encode($parameters);
