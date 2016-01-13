<?php echo \txt\Template::render('generic.json', [
  'name' => 'TXT',
  'description' => 'a super-simple pastebin',
  'usage' => "cat FILENAME | curl -F 'data=<-' $domain",
  'more-info' => $domain,
]);