RESTful-Web-Service
------------------
This is a RESTful Web Service using PHP, JSON and mysql. 

It was developed for an app (game) which basically consists in evaluating synchronous finger movements of two persons on the touch screen of two paired smartphones.

**Web Service Funtions**

Request to Play

	//Send a petition to a user to play
	/json/game

	Post Variables:
	  action = 'request_to_play'
	  user_id = '' --> id of the user that sends the request
	  request_user_id = '' --> id of the user requested
	  type_game = '' --> 1 --> 2_minut game
			     2 --> no_time game

	Return Values:
		{ "id_request": "value" }
		{ "status": "crash" } --> Only just when the server or the database presents problems	


Get Request to Play 

	//See if there is any play petition for a user
	/json/game

	Get Variables:
	  action = 'get_request_play'
	  user_id = '' --> id of the user that wants to see if there is any play petition for him

	Return Values:
		
		{
		   "result" : "true",
		   "requests" :
			[
				{ "id_request": "value", "nickname" : "value", "type_game" : "value"}
			]	
		}

		{ "result" : "false" }	--> There is not any petition	

		{ "status": "crash" } --> Only Just when server or database presents problems


See if the request to play was accepted
```
  //See if any player accepted the play request sent
  /json/game

	Get Variables:
	  action = 'see_if_accepted
	  id_request = 'value' --> this value was returned when request_to_play was sent

	Return Values:
	
		{ "accepted" : "yes", "id_play" : "value" }
		{ "accepted" : "no" }	
		{ "status": "crash" } --> Only Just when server or database presents problems
```

Accept Request
```
  //Accept a request to play
	/json/game

	Post Variables:
	  action = 'accept_request'
	  id_request = 'value' --> This value was returned when get_request_play function was executed

	Return Values:
		
		{ "id_play" : "value" }		
		{ "status": "crash" } --> Only Just when server or database presents problems
```
Cancel Request

	//Cancel a request (mainly if a user request was not accepted by anyone)
	//When the time of waiting for a response expires

	/json/game

	Post Variables:
	  action = 'cancel_request'
	  id_request = 'value'  --> this value was returned when sent request_to_play

	Return Values:
		
		{ "status" : "success" }		
		{ "status": "crash" } --> Only Just when server or database present problems



Process Vector

	//Function executed every time there is a vector of samples to send
	/json/game	

	Post Variables:
		action = 'process_vector'
		user_id = '' --> id of the user that send the vector
		id_play = '' --> this value was returned when accepted a request or executed see_if_accepted and was accepted
		type_game = ''
		num_vector = '' --> the number of this vector
		vector = '' --> an array cotaning the samples of touches in a period of time


	Return Values:

		{ "status" : "abandoned", "message" : "Player abandoned the game" }  --> The opponent abandoned the play	
		
		{ "status" : "stopped", "message" : "Player lost connection" }  --> The opponent had problems with the connection
		
		{ "status" : "finished", "score" : "value" }  --> In a play without limit the opponent wanted to finish the play. The total scored of the game
								  is returned.

		{ "status" : "OK", "score" : "value" }	--> the vector was processed and the score until that moment was returned

		{ "status": "crash" } --> Only just when server or database presents problems


Finish Play

	//Finish the play when the time of the play had expired or when a game without limit had finished
	/json/game
		
	Post Variables:
		action = 'finish_play'
		user_id = '' --> id of the user that send the vector
		id_play = '' --> this value was returned when accept a request or execute see_if_accepted and was accepted
		type_game = ''

	Return Values:
		
		{ "status" : "success", "score" : "value" }

		{ "status": "crash" } --> Only Just when server or database presents problems


