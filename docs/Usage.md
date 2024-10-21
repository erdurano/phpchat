# Installation

If your system have PHP, composer and XDebug;

```
composer install
```

If your system does not have those please install before.

# Running

```
php -S localhost:8888 -t public
```

Alternatively, you can use VSCode launcher with supplied launch.json file

# Endpoints

## /groups
Accepts: GET, POST

### GET /groups
Gets recorded group info from database.
#### request schema:
None

#### response schema:
##### status: 200(OK)
```json
[
  {
    "id": int,
    "group_name": string
  },
  ...  
]
```


### POST /groups
Creates new record and returns created group
#### request schema:
```json
{
  "group_name": string
}
```

#### response schema:
##### status:201(Created)
```json
{
"id": int,
"group_name": string
}
```

#### error schema:
##### status: 409(Conflict)
```json
{
  "error": "A group named 'group_name' already exists"
}
```

## /groups/{group_id:int}/members
Accepts: GET, POST



### GET /groups/{group_id:int}/members
Gets membership info from database
#### request schema:
None

#### response schema:
##### status: 200(OK)
```json
{
	"id": int,
	"group_name": string,
	"members": [
		{
			"id": int,
			"group_name": string
		}
	]
}
```

#### error schema
```json
{
  "error": "'user_name' is already member of 'group'."
}
```

### POST /groups/{group_id:int}/members
Adds a username to group members, creates user  for username if not exists.
#### request schema:
```json
{
  "user_name": string
}
```

#### response schema:
##### status: 201(Created)
```json
{
  "group_id": int,
  "group_name": string,
  "members": {
    "user_id": id,
    "user_name": string
  }
}
```

#### error schema:
##### status: 409(Conflict)
```json
{
  "error": "'user_name' is already member of 'group_name'."
}
```



## /groups/{group_id:int}/messages
Accepts: GET, POST
### GET /groups/{group_id:int}/messages
Gets messages sent to a specific group

> This endpoint also accepts a query parameter named 'since' to query API for messages that comes after specific time. 'since' parameter should adhere to YYYY-MM-DD-hh-mm-ss format. All times according to the UTC.
#### request schema:
None
#### response schema:
##### status: 200(OK)
```json
{
  "group_id": int,
  "group_name": string,
  "messages": [
    {
      "sender": {
        "id": int,
        "user_name": string
      },
      "content": string,
      "created_at": string "YYYY-MM-DD hh:mm:ss"
    },
    ...
  ]
}
```

### POST /groups/{group_id:int}/messages
sends message to group and returns sent message
#### request schema:
```json
{ 
  "user_name": str,
  "message" : str
}
```

#### response schema:
##### status: 201(Created)
```json
{
  "group_id": int,
  "group_name": str,
  "message": {
    "sender": {
      "id": int,
      "user_name": str
    },
    "content": str,
    "created_at": string "YYYY-MM-DD hh:mm:ss"
  }
}
```

#### error schema:
##### status: 400(Bad Request)
```json
{
  "error": "Malformed request.",
  "request_schema": {
    "user_name": "string type",
    "message": "string type"
  }
}
```
