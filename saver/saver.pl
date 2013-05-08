#!/usr/bin/perl

use WWW::Mechanize;

main($ARGV[0], $ARGV[1]);

sub main
{
	# parameters
	($url, $i) = @_;
	if( not $url ){
		pr("Usage: saver.pl url");
		return;
	}
	if( not $i ){
		$i = 1;
	}
	# create and set browser
	if( not $browser = WWW::Mechanize->new(autocheck => 0, noproxy => 1, stack_depth => 0) ){
		pr("Can not create WWW::Mechanize");
		return;
	}
	$browser->agent_alias("Windows Mozilla");
	$browser->timeout(5);
	# loop through all strips
	for( $browser->get($url); $url ne ""; $browser->get($url) ){
		# check if strip is okay
		pr("Parsing $url $i");
		if( $browser->status() != 200 ){
			pr("	Bad status: " . $browser->status());
			next;
		}
		# find next strip
		$url = "";
		if( $link = $browser->find_link(name_regex => qr#[nN][eE][xX][tT]#) ){
			$url = $link->url_abs();
		}
		if( $link = $browser->find_link(name_regex => qr#"[nN][eE][xX][tT]"#) ){
			$url = $link->url_abs();
		}
		if( $link = $browser->find_link(text_regex => qr#[nN][eE][xX][tT]#) ){
			$url = $link->url_abs();
		}
		if( $link = $browser->find_link(text_regex => qr#"[nN][eE][xX][tT]"#) ){
			$url = $link->url_abs();
		}
		# extract all images
		@images = (
			$browser->find_all_images(),
			$browser->find_all_links(url_regex => qr#\.(gif|jpe?g|png|swf|bmp|lbm|pcx|tiff?|tga)$#i)
		);
		foreach $link (@images){
			$link = $link->url_abs();
			$file = $link;
			$file =~ s#^.*[?&=/]([^?&=/]*)$#\1#im;
			myMirror($link, sprintf("%04d - $file", $i));
		}
		$i++;
	}
}

sub pr
{
	my ($text) = @_;
	print "$text\n";
	open(FO, ">>log.txt");
	print FO "$text\n";
	close(FO);
}

sub myMirror
{
	my ($url, $file) = @_;
	if( -e $file ){
		pr("	Already exists: $file from $url");
		return;
	}
	do{
		$browser->get($url);
		if( $browser->status() != 200 ){
			pr("	Can not access: $file from $url");
			return;
		}
		if( $browser->is_html() ){
			pr("	Content is HTML: $file from $url");
			return;
		}
		if( length($browser->res->decoded_content) == 0 ){
			pr("	Zero sized reply, retrying: $file from $url");
		}
	}while( length($browser->res->decoded_content) == 0 );
	if( not open FO, ">$file" ){
		pr("	Can not create: $file from $url");
		return;
	}
	binmode FO;
	print FO $browser->res->decoded_content || $browser->content;
	close FO;
	pr("	Saved: $file from $url");
}
