#!/usr/bin/perl

#############
# constants #
#############

my $timeout = 5;
my @checkurls = (
	"http://google.com",
	"http://en.wikipedia.org",
	"http://4chan.org"
);
my @saveurls = (
	"http://192.168.1.1/Docsis_system.htm",
	"http://192.168.1.1/Docsis_signal.htm",
	"http://192.168.1.1/Docsis_status.htm",
	"http://192.168.1.1/Docsis_log.htm"
);

############
# includes #
############

use File::Basename;
use Win32::Process;
use WWW::Mechanize;

###############
# getDateTime #
###############

sub getDateTime
{
	my ($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime(time);
	$year += 1900;
	$mon += 1;
	return ($year, $mon, $mday, $hour, $min, $sec);
}

######
# pr #
######

sub pr
{
	# get text, date and time for printing
	my ($text) = @_;
	my ($year, $mon, $day, $hour, $min, $sec) = getDateTime();
	# print text to logfile
	my $file = sprintf("%04d%02d%02d.txt", $year, $mon, $day);
	$text = sprintf("%04d-%02d-%02d, %02d:%02d:%02d: %s\n", $year, $mon, $day, $hour, $min, $sec, $text);
	open(FO, ">>$file") or return;
	print FO $text;
	close(FO);
}

#############
# myMirror #
#############

sub myMirror
{
	# get parameters
	my ($browser, $url, $file) = @_;
	# if file already exists just quit
	if( -e $file ){
		return;
	}
	# get file from server
	my $response = $browser->get($url);
	# return if status ain't okay
	if( $browser->status() != 200 ){
		pr(" [!] Can not access $url to save to $file");
		return;
	}
	# save file
	open(FO, ">$file") or {pr(" [!] Can not create $file"), return};
	binmode(FO);
	print FO $response->decoded_content;
	close FO;
}

########
# main #
########

	# set priority
	my $process;
	if( Win32::Process::Open($process, Win32::Process::GetCurrentProcessID(), 0) ){
		$process->SetPriorityClass(IDLE_PRIORITY_CLASS);
	}
	# create browser
	my $browser = WWW::Mechanize->new(autocheck => 0, noproxy => 1, stack_depth => 0) or die(" [!] Cannot create new WWW::Mechanize");
	$browser->agent_alias('Windows Mozilla');
	$browser->timeout($timeout);
	# get check pages
	foreach my $url (@checkurls){
		my $response = $browser->get($url);
		if( $browser->status() == 200 ){
			exit;
		}
		pr(" [!] Can not access $url");
	}
	pr("[!] Couldn't access any of the check URLs, saving modem info");
	# write error message and get save pages
	my ($year, $mon, $day, $hour, $min, $sec) = getDateTime();
	my $dir = sprintf("%04d%02d%02d", $year, $mon, $day);
	my $prefix = sprintf("%02d-%02d", $hour, $min);
	mkdir($dir);
	foreach my $url (@saveurls){
		$file = basename($url);
		myMirror($browser, $url, "$dir/$prefix-$file");
	}
