const request = require("request");
const express = require("express");
const bodyParser = require("body-parser");
const app = express();

const confirmationToken = "cfdf903b";
const apiToken = "85d05dc559a8afe376f30cea1c6ec31b01f540d46fda23cdd201eee9046855527a43e183e0b5bbb8ad2f2";
const vkApiUrl = "https://api.vk.com/method/";
const port = 8000;

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

app.post("/callback", (req, res) => {
	let data = req.body;
	let user_id = data.object.user_id;
	console.log(data);

	switch (data.type) {
		case "confirmation":
			console.log("confirmation");
			res.send(confirmationToken);
			break;
		case "group_leave":
			console.log("group_leave");
			request.post(vkApiUrl + "messages.send", { 
			form: {
				'message': "Надеюсь, тебе я был полезен, возвращайся, когда ещё будет нужна моя помощь.", 
				'user_id': user_id, 
				'access_token': apiToken, 
			}, json:true }, () => {});
			res.send("ok");
			break;
		case "group_join":
			console.log("group_join");
			request.post(vkApiUrl + "messages.send", { 
			form: {
				'message': "Благодарю за подписку", 
				'user_id': user_id, 
				'access_token': apiToken, 
			}, json:true }, () => {});
			res.send("ok");
			break;
		case "message_new":
			console.log("message_new");
			request.post(vkApiUrl + "groups.isMember", { 
				form: {
					'group_id': data.group_id, 
					'user_id': user_id, 
					'access_token': apiToken, 
				}, json:true
			}, (err, response, body) => {
				console.log(body);
				switch (body.response) {
					case 1:
						request.post(vkApiUrl + "messages.send", { 
						form: {
							'message': data.object.body, 
							'user_id': user_id, 
							'access_token': apiToken, 
						}, json:true }, () => {});
						break;
					default:
						request.post(vkApiUrl + "messages.send", { 
						form: {
							'message': "Для работы с сервисом нужно быть подписчиком сообщества.", 
							'user_id': user_id, 
							'access_token': apiToken, 
						}, json:true }, () => {});
						break;
				}
			});
			res.send("ok");
			break;
		default:
			console.log("no data type");
			res.send("ok");
			break;
	}
});

app.listen(port);
