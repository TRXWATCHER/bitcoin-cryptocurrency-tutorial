<?php 
use IEXBase\TronAPI\Tron;
use IEXBase\TronAPI\Support;
use Protocol\Transaction\Contract\ContractType;

include_once "../libraries/vendor/autoload.php";
include_once("html_iframe_header.php");
include_once("tron_utils.php");

//include all php files that generated by protoc
$dir   = new RecursiveDirectoryIterator('protobuf/core/');
$iter  = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($iter, '/^.+\.php$/', RecursiveRegexIterator::GET_MATCH); // an Iterator, not an array

foreach ( $files as $file ) {
	
	if (is_array($file)) {
		foreach($file as $filename) {
			include $filename;
		}
	} else {
		include $file;
	}
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
		
		$tx = new \Protocol\Transaction();
		$parsedRaw =  new \Protocol\Transaction\Raw();
		$parsedRaw->mergeFromString(hex2str($_POST['raw_data']));
		
		$txId = hash("sha256", $parsedRaw->serializeToString());
			
		$tx->setRawData($parsedRaw);
		$signature = Support\Secp::sign($txId, $_POST['privkey']);
		$tx->setSignature([hex2str( $signature )]);
	
    ?>
        <div class="alert alert-success">
			<h6 class="mt-3">Raw Tx Hex</h6>
			<textarea class="form-control" rows="5" id="comment" readonly><?php echo str2hex($tx->serializeToString());?></textarea>
			
			
			<h6 class="mt-3">Tx Byte Size</h6>
			<input class="form-control" rows="5" id="comment" readonly value="<?php echo $tx->byteSize();?>"></textarea>
			
			<h6 class="mt-3">Consume Bandwidth</h6>
			<input class="form-control" rows="5" id="comment" readonly value="<?php echo $tx->byteSize() + 64;?>"></textarea>
			
			<h6 class="mt-3">Tx Id</h6>
			<input class="form-control" rows="5" id="comment" readonly value="<?php echo $txId;?>"></textarea>
		</div>
<?php 
    } catch (Exception $e) {
        $errmsg .= "Problem found. " . $e->getMessage();

    }
} 

if ($errmsg) {
?>
    <div class="alert alert-danger">
        <strong>Error!</strong> <?php echo $errmsg?>
    </div>
<?php
}
?>
<form action='' method='post'>

    <div class="form-group">
        <label for="raw_data">Raw Data Hex:</label>
        <input class="form-control" type='text' name='raw_data' id='raw_data' value='<?php echo $_POST['raw_data']?>'>
    </div>
	
	<div class="form-group">
        <label for="privkey">Private Key:</label>
        <input class="form-control" type='text' name='privkey' id='privkey' value='<?php echo $_POST['privkey']?>'>
    </div>
   
    <input type='submit' class="btn btn-success btn-block"/>
</form>
<?php
include_once("html_iframe_footer.php");