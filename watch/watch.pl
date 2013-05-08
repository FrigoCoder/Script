#!/usr/bin/perl



############
# includes #
############

use File::Basename;
use File::Copy;
use Win32::Process;
use WWW::Mechanize;



######
# pr #
######

sub pr

{

# get text, date and time for printing
my ($text) = @_;
my ($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime(time);
$year += 1900;
$mon += 1;

# print text to screen
print "$text\n";

# print text to logfile
my $filename = sprintf("%4d-%02d-%02d.txt", $year,$mon, $mday);
mkdir("log");
open(FO, ">>log/$filename");
my $filetext = sprintf("%02d:%02d:%02d: %s\n", $hour, $min, $sec, $text);
printf FO $filetext;
close(FO);

};



#############
# my_mirror #
#############

sub my_mirror

{

# get parameters
my ($browser, $url, $file) = @_;

# if file already exists just quit
if( -e $file ){
	return;
};

# get file
my $response = $browser->get($url);

# if status ain't okay delete the file and return
if( $browser->status() != 200 ){
	unlink($file);
	pr(" [!] Can not access $url to save to $file");
	return;
};

# save file
open FO, ">$file" or {pr(" [!] Can not create $file"), return};
binmode FO;
print FO $response->decoded_content;
close FO;
pr(" [+] Saved $url to $file");

};



########
# main #
########

# say hello
pr("");
pr("#################");
pr("# watch.pl by F #");
pr("#################");
pr("");

# set priority
my $process;
if( Win32::Process::Open($process, Win32::Process::GetCurrentProcessID(), 0) ){
	$process->SetPriorityClass(IDLE_PRIORITY_CLASS);
};

# get parameters
if( @ARGV < 1 ){
	die(" [-] Usage: watch.pl URL [delay] [timeout]");
};
my $url = $ARGV[0];
$url =~ s#^(http://)*#http://#s;
my $delay = @ARGV < 2 ? 30 : $ARGV[1];
my $timeout = @ARGV < 3 ? 10 : $ARGV[2];

# get server, board and thread from url
my ($server, $board, $thread);
if( $url =~ m#([^\./]+)\.[^\./]+/([^\./]+)/[^0-9]*([0-9]+)[^0-9]*$# ){	# etc.domain.tld/board/whatever/numthread&whatever#whatever
	($server, $board, $thread) = ($1, $2, $3);
}
elsif( $url =~ m#([^\./]+)\.[^\./]+/[^0-9]*([0-9]+)[^0-9]*$# ){	# etc.domain.tld/whatever/numthread&whatever#whatever
	($server, $board, $thread) = ($1, "none", $2);
}
else{
	pr(" [-] Can not parse URL $url");
	exit;
};

# create browser
my $browser = WWW::Mechanize->new(autocheck => 0, noproxy => 1, stack_depth => 0) or die(" [-] Cannot create new WWW::Mechanize");
$browser->agent_alias('Windows Mozilla');
$browser->timeout($timeout);

# print info
pr(" [~] Server: $server");
pr(" [~] Board: /$board/");
pr(" [~] Thread: $thread");

# get thread
pr(" [+] Getting thread $server/$board/$thread from $url");
my $response = $browser->get($url);

# if thread does not exist, just quit
if( $browser->status() != 200 ){
	pr(" [-] Thread $server/$board/$thread at $url does not exist or an error occured");
	exit;
};

# make directories
mkdir("$server");
mkdir("$server/$board");
mkdir("$server/$board\_tmp");
mkdir("$server/$board\_tmp/$thread");
mkdir("$server/$board\_tmp/$thread/res");

# loop while thread is alive
while( $browser->status() != 404 ){

	# wait while thread is temporarily unavailable
	if( $browser->status() != 200 ){

		pr(" [!] Thread $url is temporarily unavailable");

	}else{

		# get resources (stylesheets, thumbnails, etc) and (linked) images
		my @resources = (
			$browser->find_all_links(url_regex => qr#\.([cC][sS][sS]|[jJ][sS])$#),
			$browser->find_all_images()
		);
		my @images = $browser->find_all_links(url_regex => qr#\.([gG][iI][fF]|[jJ][pP][eE]?[gG]|[pP][nN][gG]|[sS][wW][fF]|[bB][mM][pP]|[lL][bB][mM]|[pP][cC][xX]|[tT][iI][fF][fF]?|[tT][gG][aA])$#);

		# start rewriting page
		$content = $response->decoded_content;

		# rewrite resources
		foreach my $link (@resources){
			$tail = basename($link->url_abs());
			$content =~ s#="?\s*[^="]*$tail\s*"?#="res/$tail"#gs;
		};

		# rewrite images
		foreach my $link (@images){
			$tail = basename($link->url_abs());
			$content =~ s#="?\s*[^="]*$tail\s*"?#="$tail"#gs;
		};

		# write file
		my $file = "$server/$board\_tmp/$thread/$thread.html";
		if( open FO, ">$file" ){
			binmode FO, ":utf8";
			print FO $content;
			close FO;
		}else{
			pr(" [!] Can not create $file");
		};

		# save all resources
		foreach my $link (@resources){
			my_mirror($browser, $link->url_abs(), "$server/$board\_tmp/$thread/res/" . basename($link->url_abs()));
		};

		# save all images
		foreach my $link (@images){
			my_mirror($browser, $link->url_abs(), "$server/$board\_tmp/$thread/" . basename($link->url_abs()));
		};

	};

	# sleep
	pr(" [+] Sleeping for $delay seconds");
	sleep($delay);

	# reload thread
	pr(" [+] Reloading thread $server/$board/$thread");
	$response = $browser->get($url);

};

# move thread to its final resting place
pr(" [+] Thread $server/$board/$thread is dead, moving.");
move("$server/$board\_tmp/$thread", "$server/$board/$thread");

# say goodbye
pr(" [+] Finished.");
