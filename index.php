<?php 
set_time_limit(0);
header('Content-type: text/html; charset=utf8');
libxml_use_internal_errors(true);
class pars{
/*создание базы данных*/
	public $login_db = 'root';
	public $pass_db = '';
	public $db_name = 'venera_pars';
	function db_con(){
		$con_to_ms = mysqli_connect("localhost", $this->login_db, $this->pass_db);
		mysqli_query($con_to_ms,'CREATE DATABASE '.$this->db_name);
		$con = mysqli_connect("localhost", $this->login_db, $this->pass_db, $this->db_name);
		if (! $con) {
			echo 'Ошибка подключения к бд';
		}
		mysqli_query($con,'ALTER DATABASE venera_pars CHARACTER SET utf8 COLLATE utf8_general_ci');
		$sql = 'CREATE TABLE goods (
				id int AUTO_INCREMENT,
				PRIMARY KEY (id),
				url varchar(100),
				type_create varchar(255),
				quality_one varchar(40),
				collect varchar(40),
				form varchar(40),
				color varchar(40),
				design varchar(40),
				country varchar(40),
				quality varchar(40),
				composition varchar(40),
				thickness varchar(40),
				weight varchar(40),
				height varchar(40),
				price longtext,
				img longtext)';
		mysqli_query($con,'ALTER goods tablename CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
		mysqli_query($con,$sql);
		return $con;
	}
/*создание базы данных*/

/*Авторизация*/
	public $post = array(
		'auth[email]' => 'sinai41@mail.ru',
	    'auth[password]' => '353398');
	public $url_login = 'http://venera-carpet.ru/user/auth.html';
	function get_curl($url){
			$ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $this->url_login);
		    curl_setopt($ch, CURLOPT_HEADER, 1);
		    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
		   	curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . "/cookie.txt");
			curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . "/cookie.txt");
		  	curl_exec($ch);
		  	curl_setopt($ch, CURLOPT_URL, $url);
		  	$this->html = curl_exec($ch);
			curl_close($ch);
			return $this->html;
		}

/*Авторизация*/

	function get_xpath($html){
		if ( ! $html ) {
        return $html;
    }
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		return $xpath;
	}

	function get_img($xpath){
		$images = $xpath->query("//img[contains(@class,'zoom_img')]");
		$img = '';
		foreach ($images as $image) {
			if ($img != '' ){
				$img = $img . ',';
			}
			$img_url = 'http://venera-carpet.ru' . $image->getAttributeNode('src')->value;
			$img_save = $this->get_img_asist($img_url);
			file_put_contents(__DIR__ . '/img/' . preg_replace('/[\/]/','',$image->getAttributeNode('src')->value) . '.jpg', $img_save);
			$img = $img . $img_url;
		}
		return $img;
	}
	function get_img_asist($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result=curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	function get_price($xpath){
		$prices = $xpath->query("//td[contains(@class,'price')]");
		$sizes = $xpath->query("//td[1]");
		$price_json = '{';
		$i=0;
		foreach ($prices as $price){
			if ($price_json != '{' ){
				$price_json = $price_json . ',';
			}
			$price_json =  $price_json . '"' . $sizes->item($i)->nodeValue . '":"' . preg_replace('/[pр ]/','',$price->nodeValue) . '"';
			$i++;
		}
		$price_json = $price_json . '}';
		return $price_json;
	}
	function convert_price($price){
		return str_ireplace(',','\\,',str_ireplace('"','\\"',$price));
	}

	function get_sql($xpath,$url){
		$url = $url;
		$type_create = 'NULL';
		$quality_one = 'NULL';
		$collect = 'NULL';
		$form = 'NULL';
		$color = 'NULL';
		$design = 'NULL';
		$country = 'NULL';
		$quality = 'NULL';
		$composition = 'NULL';
		$thickness = 'NULL';
		$weight = 'NULL';
		$height = 'NULL';
		$price = 'NULL';
		$img = 'NULL';
		$img = $this->get_img($xpath);
		$price = $this->convert_price($this->get_price($xpath,$html));
		$propertys = $xpath->query("//span[contains(@class,'property')]");
		$property_sqr = $xpath->query("//div[contains(@class,'data')]");		
		$propertys_value = $this->get_property($property_sqr->item(0)->nodeValue,$propertys);
		$i=1;
		foreach ($propertys as $property) {
			switch (trim($property->nodeValue)) {
				case 'Способ изготовления:':
					$type_create = $propertys_value[$i];
				break;
				case 'Качество1:':
					$quality_one = $propertys_value[$i];
				break;
				case 'Коллекция:':
					$collect = $propertys_value[$i];
				break;
				case 'Форма:':
					$form = $propertys_value[$i];
				break;
				case 'Код цвета:':
					$color = $propertys_value[$i];
				break;
				case 'Страна:':
					$country = $propertys_value[$i];
				break;
				case 'Качество:':
					$quality = $propertys_value[$i];
				break;
				case 'Код состава:':
					$composition = $propertys_value[$i];
				break;
				case 'Плотность:':
					$thickness = $propertys_value[$i];
				break;
				case 'Вес:':
					$weight = $propertys_value[$i];
				break;
				case 'Высота ворса:':
					$height = $propertys_value[$i];
				break;
				case 'Вес:':
					$weight = $propertys_value[$i];
				break;

				default:
					echo 'неизвестное поле:'.$property->nodeValue.'</br>';
				break;	
				case ('Код дизайна:' || 'Дизайн'):
					$design = $propertys_value[$i];
				break;
			}
			$i++;
		}
		$sql = $this->get_sql_assist($url,$type_create,$quality_one,$collect,$form,$color,$design,$country,$quality,$composition,$thickness,$weight,$height,$price,$img);
		return $sql;
	}

	function get_sql_assist($url,$type_create,$quality_one,$collect,$form,$color,$design,$country,$quality,$composition,$thickness,$weight,$height,$price,$img){
		$sql="INSERT INTO goods VALUES(null,'$url','$type_create','$quality_one','$collect','$form','$color','$design','$country','$quality','$composition','$thickness','$weight','$height','$price','$img')";
		return $sql;
	}

	function get_property($sqr,$propertys){
		$sqr = str_ireplace('Характеристики','',$sqr);
		foreach ($propertys as $property) {
			$sqr = str_ireplace($property->nodeValue,'#property#',$sqr);
		}
		$proper = explode("#property#", $sqr);
		return $proper;
	}
}

$pars = new pars();
if (!file_exists(__DIR__ . '/img')){
	mkdir(__DIR__ . '/img', 0777);
}
$con = $pars->db_con();
$xpath = $pars->get_xpath($curl);
$curl = $pars->get_curl('http://venera-carpet.ru/category/index.html?filterStep=2&changePhoto=1&word=&warehouses%5B%5D=0&warehouseType=one&priceFrom=&priceTo=');
$xpath = $pars->get_xpath($curl);
$total = preg_replace('/\D/','',$xpath->query("//div[contains(@class,'ajaxScroll')]/p")->item(0)->nodeValue);
$y=0;
$i=1;
/*получаем массив ссылок на товары*/
while ($y<=100) { 
	$catalog = file_get_contents('http://venera-carpet.ru/category/index.html?page='.$i.'&ajax=1');
	$xpath = $pars->get_xpath($catalog);
	$links = $xpath->query("//a[contains(@class,'img')]");
	$chek = 0;
	foreach ($links as $link) {
		$goods_links[$y] = 'http://venera-carpet.ru' . $link->getAttributeNode('href')->value;
		$y++;
	}
	$i++;
}
/*получаем массив ссылок на товары*/

foreach ($goods_links as $url) {
	$goods = $pars->get_curl($url);
	$xpath = $pars->get_xpath($goods);
	$sql = $pars->get_sql($xpath,$url);
	mysqli_query($con,$sql);
}
libxml_clear_errors();
mysqli_close($con);
?>




