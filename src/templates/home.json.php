<?php echo \txt\Template::render('generic.json', [
  'name' => 'TXT',
  'description' => 'a super-simple pastebin',
  'usage' => "curl -F 'data=<-' $domain < FILENAME",
  'more-info' => $domain,
]);
