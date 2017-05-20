<?php 

mb_internal_encoding("UTF-8");

$data_json = [
	"Привет" => "Привет",
	"Здарова" => "Здарова",
	"Хай" =>  "Хай",
	"Как дела" => "Все хорошо",
	"Как сам" => "Все хорошо",
	"Хуй" => "Попрошу без мата)",
	"иди на хуй" => "Попрошу без мата)",
	"Жопа" => "Попрошу так не вырожаться))",
];

$confirmation_token = '521d1456';
$token = '34fab1de795797f81b35e8905233ec756ef35a22405d0167608a847f9f5922ca827d450574eb52ae3b9e5';
$api_key = '7ca53812a6dcac1b30f316f807354abd';

$data = json_decode(file_get_contents('php://input')); 

$group_id = $data->group_id;
$message_id = $data->object->id;
$user_id = $data->object->user_id;
$body = $data->object->body; 



$curl_handle = curl_init();
curl_setopt($curl_handle, CURLOPT_URL, "https://api.vk.com/method/groups.isMember?group_id={$group_id}&user_id={$user_id}&v=5.60"); 
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true); 
$issubscripe = json_decode(curl_exec($curl_handle), true);
curl_close($curl_handle);




switch ($data->type) {
	case 'confirmation': 
		echo $confirmation_token; 
		break;
	case 'group_leave': 
		$request_params = array( 
			'message' => "Надеюсь, тебе я был полезен, возвращайся, когда ещё будет нужна моя помощь.", 
			'user_id' => $user_id, 
			'access_token' => $token, 
			'v' => '5.0' 
		);
		file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params)); 

		echo('ok');
		break;
	case 'group_join': 
		$request_params = array( 
			'message' => "Благодарю за подписку", 
			'user_id' => $user_id, 
			'access_token' => $token, 
			'v' => '5.0' 
		);
		file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params)); 

		echo('ok');
		break;
	case 'message_new': 
		$issubscripe = $issubscripe['response'];

		if($issubscripe == 1) {
			if(file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=".$body."&appid=".$api_key."&lang=ru&units=metric")) {
				$getWeather = json_decode(file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=".$body."&appid=".$api_key."&lang=ru&units=metric"));

				$weather_message = "Город " .$body. ".<br>".
				"Сейчас на улице " . $getWeather->weather[0]->description.".<br>".
				"Скорость ветра: " . round($getWeather->wind->speed)." м/с.<br>".
				"Температура воздуха: " . round($getWeather->main->temp)."°C <br>";
			} else {
				$weather_message = 'Такого города не существует';
			}

			// Обработка банальных вопросов
			foreach ($data_json as $question => $answer) {
			  if (preg_match("/".$question."/i", $body)) {
			    $weather_message = $answer;
			  }
			}

			$request_params = array( 
				'message' => "{$weather_message}",
				'user_id' => $user_id,
				'access_token' => $token,
				'v' => '5.0' 
			); 
		} else {
			$request_params = array( 
				'message' => "Для работы с сервисом нужно быть подписчиком сообщества vk.com/okunderstand", 
				'user_id' => $user_id,
				'access_token' => $token, 
				'v' => '5.0' 
			);
		}
		
		file_get_contents('https://api.vk.com/method/messages.send?'. http_build_query($request_params));

		echo('ok');
		break;
	default:
		echo('ok');
		break;
}
?>