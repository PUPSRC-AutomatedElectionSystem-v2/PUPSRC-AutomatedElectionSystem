<?php
require_once 'src/includes/mailer-test.php';
require_once 'src/includes/classes/email-sender.php';
require_once 'src/includes/classes/email-queue.php';
require_once 'src/includes/classes/db-config.php';
require_once 'src/includes/classes/db-connector.php';
include_once 'src/includes/default-time-zone.php';

$bccs = [
    // 'zandrobandilla18@gmail.com',
    'c.ivanbandilla@gmail.com',
    'bandilla21@outlook.com',
    // 'jukajwujajajsjhxhaakfreiahwhaj@gmail.com',
    // 'jennymaelbaldoza@iskolarngbayan.pup.edu.ph',
    // 'carlftabuso@iskolarngbayan.pup.edu.ph',
    // 'biellamaermariscotes@iskolarngbayan.pup.edu.ph',
    // 'johncarlocbagwisa@iskolarngbayan.pup.edu.ph',
    // 'blessedkingmredondo@iskolarngbayan.pup.edu.ph',
    // 'jonasclydemoli@iskolarngbayan.pup.edu.ph',
    // 'jonasclydemoli@iskolarngbayan.pup.edu.ph',
    // 'jessicagbaldos@iskolarngbayan.pup.edu.ph',

    // 'ataatcarmelli@gmail.com',
    // 'kimjudeamayon468@gmail.com',
    // 'xtianseventeen@gmail.com',
    // 'jb1araojo@gmail.com',
    // 'jcbagwisa@gmail.com',
    // 'cedieziahpili@gmail.com',
    // 'paulynabueva@gmail.com',
    // 'maecarpena@gmail.com',
    // 'mthwcrvnts10@gmail.com',
    // 'sanjaychandimc@gmail.com',
    // 'markcueva6@gmail.com',
    // 'shnlydcny19@gmail.com',
    // 'adriandata242@yahoo.com',
    // 'oneryxdator@gmail.com',
    // 'deyben011003@gmail.com',
    // 'jamesydrick@gmail.com',
    // 'edanivan2712002@gmail.com',
    // 'elisseescaros@gmail.com',
    // 'ashleefajarito511@gmail.com',
    // 'seanforlanda@gmail.com',
    // 'ruwengonzalez@gmail.com',
    // 'marcnuel14@gmail.com',
    // 'hernandezgeorge640@gmail.com',
    // 'kjustimbaste12@gmail.com',
    // 'vincegiven03@gmail.com',
    // 'sofieann.lavin@gmail.com',
    // 'jeremielegrama@gmail.com',
    // 'nolilicudo@gmail.com',
    // 'kenjiemaala@gmail.com',
    // 'p.einsherinamaala@gmail.com',
    // 'lykamallo@gmail.com',
    // 'quazi8883@gmail.com',
    // 'biella.requina@gmail.com',
    // 'matibagandrei8@gmail.com',
    // 'morantebobbylloyd@gmail.com',
    // 'huhnahnavarro@gmail.com',
    // 'acee.olaso@gmail.com',
    // 'jonasoli107@gmail.com',
    // 'pachecocyrus1026@gmail.com',
    // 'perezness1973@gmail.com',
    // 'gwenplenago@gmail.com',
    // 'gakilltatsumi11@gmail.com',
    // 'redondoblessed@gmail.com',
    // 'genricredondo01@gmail.com',
    // 'dwineruiz@gmail.com',
    // 'ellejhanesaniel07@gmail.com',
    // 'solomojoseph051@gmail.com',
    // 'carltabuso69@gmail.com',
    // 'axlivantingzon@gmail.com',
    // 'aphotography018@gmail.com',
    // 'valeraarwengrace19@gmail.com',
    // 'velandoaedriel60@gmail.com',
    // 'kazumiyoro29@gmail.com',
    // 'Johnlloydaber01@gmail.com',
    // 'xyrenealega122802@gmail.com',
    // 'katelysianpoems@gmail.com',
    // 'airaamantee@gmail.com',
    // 'mariaallyzaapolonio@gmail.com',
    // 'jynneishiaquino@gmail.com',
    // 'taaarconada.nssc@gmail.com',
    // 'charlesdave0123@gamil.com',
    // 'marialourdesbaldonado@gmail.com',
    // 'lemuelbarela@gmail.com',
    // 'rlbayran@gmail.com',
    // 'dnbersabal190477@gmail.com',
    // 'auroraboreres@gmail.com',
    // 'raizaborromeo97@gmail.com',
    // 'jaiflor1123@gmail.com',
    // 'bulataojhonraven@gmail.com',
    // 'allyzallanto@gmail.com',
    // 'guiellecabi85@gmail.com',
    // 'akuroku72@gmail.com',
    // 'carpioerika2003@gmail.com',
    // 'kc.cartano724@gmail.com',
    // 'Fierby0929@gmail.com',
    // 'czhaye.yeane@gmail.com',
    // 'marcusnethancosico@gmail.com',
    // 'mialaineccruz21@gmail.com',
    // 'aizehnricx2@gmail.com',
    // 'jonathanestebandejesus01@gmail.com',
    // 'sheilaannedeleon@gmail.com',
    // 'delacruzarabella10@gmail.com',
    // 'dimchorlyn@gmail.com',
    // 'dimaculangan.chorlyn@gmail.com',
    // 'neildojeta@gmail.com',
    // 'juliusdorado123@gmail.com',
    // 'edenjdue38@gmail.com',
    // 'emralino21@gmail.com',
    // 'cedfallerkent@gmail.com',
    // 'peterwilrexeescueta@gmail.com',
    // 'ayentroi15@gmail.com',
    // 'hervinafalla@gmail.com',
    // 'fmikaellaallysa1@gmail.com',
    // 'kristinegalindez7@gmail.com',
    // 'gangoso.mikaella@gmail.com',
    // 'merciditasgelvez@gmail.com',
    // 'hendrixerronherradura@gmail.com',
    // 'ralphhonra@gmail.com',
    // 'artcelljavel26@gmail.com',
    // 'tishapamela18@gmail.com',
    // 'patricialacap2003@gmail.com',
    // 'aeyuanne28@gmail.com',
    // 'liragnoriedel@gmail.com',
    // 'llusala73@gmail.com',
    // 'laramaymarinas5@gmail.com',
    // 'medinakrishae@gmail.com',
    // 'mendiola.11.stemb@gmail.com',
    // 'billymondigo.pup11@gmail.com',
    // 'emmanuelgabrielolandria@gmail.com',
    // 'pecayofelixtristan0016@gmail.com',
    // 'joycearabella4@gmail.com',
    // 'pulidoanaroselle@gmail.com',
    // 'kimragay0918@gmail.com',
    // 'rejusocute101@gmail.com',
    // 'b17requinaarjed@gmail.com',
    // 'jemrosario24@gmail.com',
    // 'jhnbrnrdshgn@gmail.com',
    // 'samanteallysa@gmail.com',
    // 'sanjuanmeena@gmail.com',
    // 'techrafaelalmendral@gmail.com',
    // 'lasantos12309@gmail.com',
    // 'jacs0611@gmail.com',
    // 'tahumjanine@gmail.com',
    // 'tenorioileynaaa@gmail.com',
    // 'tisadoaya@gmail.com',
    // 'wayneivantolopia@gmail.com',
    // 'umaliprincessdianne022@gmail.com',
    // 'velardehannalyn@gmail.com',
    // 'gailevicuna1411@gmail.com',
    // 'andreasofiavillalobos529@gmail.com',
    // 'rchl0408@gmail.com',
    // 'ninatamparongvios@gmail.com',
    // 'yoroizumi@gmail.com',


    // 'ramillaallen25@gmail.com',
    // 'zsabre34@gmail.com',
    // 'scrublord4s3@gmail.com',
    // 'sc58002@gmail.com',
    // 'christ@personal.com',
    // 'kimccrdr@gmail.com',
    // 'eian.enriquez13@gmail.com',
    // 'paulivanarazo66@gmail.com',
    // 'rsamadan2021@gmail.com',
    // 'kenleyavelino2003@gmail.com',
    // 'arvicjames@gmail.com',
    // 'johnpaulrabac11@gmail.com',
    // 'arvicjames@yahoo.com',
    // 'operiokeisha2130@gmail.com',
    // 'kevincharlespacho@gmail.com',
    // 'cossycamille02@gmail.com',
    // 'iversonjacintoooo@gmail.com',
    // 'canaelbrian@gmail.com',
    // 'carantojigz@gmail.com',
    // 'jinkycabilin57@gmail.com',
    // 'mlyward9@gmail.com',
    // 'jrdansmith810@gmail.com',
    // 'sanjuankenneth4@gmail.com',
    // 'laigo.bellen@gmail.com',

    // 'doelzita5@gmail.com',
    // 'r.berton@yandex.com',
    // 'Dikster.greedy@proton.me',

];

require_once FileUtils::normalizeFilePath('src/includes/classes/query-handler.php');

$conn = DatabaseConnection::connect();

$queryExecutor = new QueryExecutor($conn);

$voter_query = "SELECT email FROM voter WHERE account_status = 'verified' AND role = 'student_voter'";
$result = $queryExecutor->executeQuery($voter_query);

$verified_voters_email = [];

while ($row = $result->fetch_assoc()) {
    $verified_voters_email[] = $row['email'];
}

// print_r($verified_voters_email);
// print_r($bccs);

$send_email = new EmailSender($mail);
$emails = $send_email->sendElectionCloseEmail($verified_voters_email);

$currentDateTime = date('Y-m-d H:i:s');

$queueMail = new EmailQueue();

$hash = EmailQueue::generateHash();

EmailQueue::insertQueue($emails, $currentDateTime, $hash);

// $emails = $queueMail->getQueue();

// print_r($emails);

// foreach ($emails as $email) {
//     echo $send_email->sendQueuedMail($email['content']);
// }
