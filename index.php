<?php
function algo_words($input, $length=3, $sep='', $complex=false, $wordlist='popular-edit.txt') {
  $hash = hash("SHA256", $input);
  $maxLineLength = 256;
  $line_count = 0;
  $handle = fopen($wordlist, "r");
  while (!feof($handle)) {$l=fgets($handle);$line_count++;}
  fclose($handle);
  $chr_len = strlen(dechex($line_count));
  $wpos = array();
  for ($i=0; $i<$length; $i+=1) {
    $wpos[$i] = hexdec(substr($hash, $i*$chr_len, $chr_len)) % $line_count;
  }
  $w = array();
  $handle = fopen($wordlist, "r");
  $c = 0;
  $str = "";
  while (($line = fgets($handle, $maxLineLength)) !== false) {
    foreach($wpos as $k=>$v) {
      if ($v == $c) {
        $w[$k] = trim($line);
        $str .= trim($line).$sep;
      }
    }
    $c+=1;
  }
  ksort($w, SORT_NUMERIC);  #Must sort back by index (or result will follow the wordlist order, usually alphabetical)
  fclose($handle);
  return implode($sep, $w);
}

if (isset($_REQUEST["query"])) {
  if ($_REQUEST["query"]=="getpassword") {
    $words = algo_words($_REQUEST["input"], $_REQUEST["num_words"], $_REQUEST["sep"]);
    $a=array("result" => "success", "input"=>$_REQUEST["input"], "words" => $words);
    print(json_encode($a));
    exit;
  }
}
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<title>Password Gen</title>
		<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script>
      function get_algo() {
        $.ajax({
          url: 'index.php?query=getpassword',
          type: 'post',
          dataType: 'json',
          data: {"num_words": Math.max(1, Math.min(16, $('#algo_inp_len').val())), "sep": $('#algo_inp_sep').val(), "input": $('#algo_inp_case').val()},
          //data: {"num_words": 3, "sep": "-", "input": $('#algo_inp_case').val()},
          success: function(response) {
            $('#algo_res').html(response.words);
          }
        })
      }
      function selectElementText(el) {
          removeTextSelections();
          if (document.selection) {
              var range = document.body.createTextRange();
              range.moveToElementText(el);
              range.select();
          }
          else if (window.getSelection) {
              var range = document.createRange();
              range.selectNode(el);
              window.getSelection().addRange(range);
          }
      }
      function removeTextSelections() { // Deselects all text in the page.
          if (document.selection) document.selection.empty();
          else if (window.getSelection) window.getSelection().removeAllRanges();
      }
    </script>
    <style>
      .algo_words {
        margin: 20px;
      }
      input {
        margin: 2px 5px 0 0;
        vertical-align: middle;
      }
      .selectText {
        background-color: #CCC;
        width: fit-content;
        padding: 5px;
      }
    </style>
  </head>
  <body>
    <div class='algo_words'>
      <p>Generate consistent safe passwords form a text string:</p>
      <p><input id='algo_inp_case' size='28' placeholder='Enter text'> Separator: <input id='algo_inp_sep' size='1' value='-'></p>
      <p>Length: <input id='algo_inp_len' type='range' min='1' max='10' value='3'></p>
      <div id='algo_res' class='selectText'></div>
    </div>
    <script>
      $(document).ready(function() {
        $('.selectText').click(function(){ selectElementText(this); })
        $('[id^="algo_inp_"]').bind('input', function() {get_algo();});
        $('.selectText').click(function(){ selectElementText(this); })
        $('#algo_inp_case').focus();
        get_algo();
      });
    </script>
  </body>
</html>
