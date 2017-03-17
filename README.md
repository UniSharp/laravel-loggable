# Laravel Log Writer
This package formats exceptions before they are written to `laravel.log`.

## Installation
1. Install via composer

	```
	composer require unisharp/laravel-filemanager
	```
	
2. Set up `config/app.php`

	```
	'providers' => [
		...
		Unisharp\Loggable\LoggableServiceProvider::class,
	],
	```
	```
	'aliases' => [
		...
		'Loggable' => Unisharp\Loggable\Facades\Loggable::class,
	],
	```
	
3. Replace default exception reporter in `App\Exceptions\Handler.php`

	```
	public function report(Exception $e)
	{
		// parent::report($e);
		return \Loggable::report($e);
	}
	```

## Log display types
 * Simple log
	
	```
	404 not found. | (GET) http://your-domain/js/jquery.min.map | User ID : null | IP: 127.0.0.1
	```
	```
	Model not found. | (GET) http://your-domain/article/999 | User ID : 6 | IP: 127.0.0.1
	```
	
 * Detail log with trace and input

	```
	2016-11-17 19:03:46] local.DEBUG: {
	    "user_id": 4,
	    "ip": "::1",
	    "action_trace": {
	        "0": "Visited : OrderController | Action : create",
	        "1": "Visited : CartController  | Action : count | Type : Ajax",
	        "2": "Visited : CartController  | Action : show",
	        "3": "Visited : CartController  | Action : count | Type : Ajax",
	        "4": "Visited : OrderController | Action : create",
	        "5": "Visited : CartController  | Action : count | Type : Ajax",
	        "6": "Visited : OrderController | Action : store",
	        "FormRequest failed": {
	            "receiver_name": "",
	            "receiver_phone": "",
	            "note": "",
	            "_token": "Mvi43arsvzrqH5RuzQVPl0GdU2xVwE7FO79Lxw1A"
	        }
	    }
	}  
	```
	
## Handled exceptions

 * Detail log :
  * Form request error
 * Simple log :
  * TokenMismatchException
  * ModelNotFoundException
  * NotFoundHttpException
  * HttpException
 * Both simple log and original stack trace will be written when other exceptions occurs.
