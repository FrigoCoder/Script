<?php
// get config
include_once ("mailer.conf.php");
// get arguments
if( $argc < 3 + 1 ){
	echo ("Usage: mailer.php repository_path revision email [email2 email3 ...]");
	exit(1);
}
$repopath = $argv[1];
$repo = basename($repopath);
$rev = $argv[2];
$recipients = array();
for( $i = 3; $i < $argc; $i++ ){
	$recipients[] = $argv[$i];
}
// extract info about the particular revision
$svnlook = "chcp 65001 1> nul 2> nul && $svnpath/bin/svnlook.exe";
$author = shell_exec("$svnlook author $repopath -r $rev");
$date = shell_exec("$svnlook date $repopath -r $rev");
$log = shell_exec("$svnlook log $repopath -r $rev");
$changes = shell_exec("$svnlook changed $repopath -r $rev");
// format author
$author = preg_replace("#^[\s]*$#i", "anonymous", $author);
$author = preg_replace("#\n$#i", "", $author);
// format date
$date = preg_replace("#^(....-..-.. ..:..:..).*$#i", "$1", $date);
$date = preg_replace("#\n$#i", "", $date);
// format log
$log = preg_replace("#^#im", "	", $log);
$log = preg_replace("#\n$#i", "", $log);
// format changes
$changes = preg_replace("#^A *#im", "Added     ", $changes);
$changes = preg_replace("#^C *#im", "Copied    ", $changes);
$changes = preg_replace("#^D *#im", "Deleted   ", $changes);
$changes = preg_replace("#^U *#im", "Modified  ", $changes);
$changes = preg_replace("#^#im", "	", $changes);
$changes = preg_replace("#\n$#i", "", $changes);
// construct mail
$subject = "Repository $repo changed to revision $rev by $author at $date";
$message = "Repository: $repo\n" . "Revision: $rev\n" . "Author: $author\n" . "Date: $date\n" . "Log:\n" . "$log\n" . "Changes:\n" . "$changes\n";
// construct mail and send it
include_once "class.phpmailer.php";
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPDebug = 1;
$mail->SMTPAuth = true;
$mail->Host = $host;
$mail->Username = $user;
$mail->Password = $pass;
$mail->From = $user;
$mail->FromName = $user;
$mail->Subject = $subject;
$mail->Body = $message;
$mail->CharSet = "UTF-8";
foreach( $recipients as $recipient ){
	$mail->AddAddress($recipient);
}
$mail->Send();
?>
