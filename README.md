# Inventor system

## To launch the API on your local machine:
* `php artisan jwt:secret`
* `php artisan key:generate`
* Launch a local MySQL server and create `.env` file based on `.env.example` and edit it to match your configuration
* `php artisan migrate`
* `php artisan DB:seed`
* `php artisan serve`

## Routes

- [User](#user)
  * [GET](#get)
  * [POST](#post)
  * [PUT](#put)
  * [DELETE](#delete)
- [Authorization](#authorization)
  * [GET](#get-1)
  * [POST](#post-1)
- [Company](#company)
  * [GET](#get-2)
  * [POST](#post-2)
  * [PUT](#put-1)
  * [DELETE](#delete-1)
- [Gear](#gear)
  * [GET](#get-3)
  * [POST](#post-3)
  * [PUT](#put-2)
  * [DELETE](#delete-2)
- [Requests](#requests)
  * [GET](#get-4)
  * [POST](#post-4)
  * [DELETE](#delete-3)
- [Password reset](#password-reset)
  * [POST](#post-5)
- [History](#history)
  * [GET](#get-5)

### User
#### GET

<strong>URI: `GET` http://localhost:8000/api/users/all </strong>

Function: Returns all users in the database. Only for users with role: 1

Parameters:

|Parameter|Type  |Description     |Required|
|---------|------|----------------|--------|
|`search` |string|Search query    |false   |
|`company`|string|Company of users|false   |

* Success response:
  * Code: 200 OK
  * Content: a list of all users
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: Unauthorized
* Error response (not found):
  * Code: 404 Not found
  * Content: empty

<strong>URI: `GET` http://localhost:8000/api/users </strong>

Function: Returns all users within the user company

Parameters:

|Parameter|Type  |Description |Required|
|---------|------|------------|--------|
|`search` |string|Search query|false   |

* Success response:
    * Code: 200 OK
    * Content: a list of all users
* Error response:
    * Code: 404 Not found
    * Content: empty

<strong>URI: `GET` http://localhost:8000/api/users/{id} </strong>

Function: Returns the user with the specified id.

* Success response:
    * Code: 200 OK
    * Content: user with the specified id
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, user not found"

#### POST

<strong>URI: `POST` http://localhost:8000/api/users </strong>

Function: Adds a user to the database and emails him to set a password. Only for users with roles: 1.

Parameters:

|Parameter              |Type  |Description                       |Required|
|-----------------------|------|----------------------------------|--------|
|`first_name`           |string|The first name of the user        |true    |
|`last_name`            |string|The last name of the user         |true    |
|`email`                |string|Email of the user                 |true    |
|`company_id`           |int   |User's company id                 |true    |
|`role`                 |int   |User's role (0: regular, 1: admin)|true    |

* Success response:
    * Code: 201 Created
    * Content: "Password creation email has been sent."
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: Unauthorized
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error specification

#### PUT

<strong>URI: `PUT` http://localhost:8000/api/users/{id} </strong>

Function: Updates the user with the specified id.

Parameters:

|Parameter              |Type  |Description                       |Required|
|-----------------------|------|----------------------------------|--------|
|`first_name`           |string|The first name of the user        |false   |
|`last_name`            |string|The last name of the user         |false   |
|`email`                |string|Email of the user                 |false   |
|`role`                 |int   |User's role (0: regular, 1: admin)|false   |

* Success response:
    * Code: 200 OK
    * Content: The updated user
* Error response (user not found):
    * Code: 404 Not found
    * Content: "Sorry, user not found"
* Error response (bad parameters):
  * Code: 400 Bad request
  * Content: Error specification

#### DELETE

<strong>URI: `DELETE` http://localhost:8000/api/users/{id} </strong>

Function: Deletes the user with the specified id.

* Success response:
    * Code: 200 OK
    * Content: "User deleted successfully"
* Error response (user not found):
    * Code: 404 Not found
    * Content: "Sorry, user not found"
* Error response (user has gear):
    * Code: 400 Bad request
    * Content: "User cannot be deleted, because user has gear"

### Authorization
#### GET

<strong>URI: `GET` http://localhost:8000/api/auth/user-profile </strong>

Function: Returns the logged in user's data.

* Success response:
    * Code: 200 OK
    * Content: logged in user's data

#### POST

<strong>URI: `POST` http://localhost:8000/api/auth/login </strong>

Function: Logs in the user and provides a bearer token.

Parameters:

|Parameter              |Type  |Description                       |Required|
|-----------------------|------|----------------------------------|--------|
|`email`                |string|Email of the user                 |true    |
|`password`             |string|Password of the user              |true    |

* Success response:
    * Code: 200 OK
    * Content: Access token and user's data
* Error response:
    * Code: 401 Unauthorized
    * Content: Unauthorized

<strong>URI: `POST` http://localhost:8000/api/auth/logout </strong>

Function: Logs out the user.

* Success response:
    * Code: 200 OK
    * Content: "User successfully signed out"

<strong>URI: `POST` http://localhost:8000/api/auth/refresh </strong>

Function: Refreshes user's token.

* Success response:
    * Code: 200 OK
    * Content: New access token and user's data

### Company
#### GET

<strong>URI: `GET` http://localhost:8000/api/companies </strong>

Function: Returns all companies' data. Only for users with role: 1.

Parameters:

|Parameter|Type  |Description |Required|
|---------|------|------------|--------|
|`search` |string|Search query|false   |

* Success response:
    * Code: 200 OK
    * Content: A list of all companies
* Error response:
    * Code: 401 Unauthorized
    * Content: "Not authorized"

<strong>URI: `GET` http://localhost:8000/api/companies/{id} </strong>

Function: Returns the company with the specified id

* Success response:
    * Code: 200 OK
    * Content: The company with the specified id
* Error response:
    * Code: 404 Not Found
    * Content: "Sorry, company not found"

#### POST

<strong>URI: `POST` http://localhost:8000/api/companies </strong>

Function: Creates a new company. Only for users with role: 1.

Parameters:

|Parameter|Type  |Description        |Required|
|---------|------|-------------------|--------|
|`name`   |string|Name of the company|true    |

* Success response:
    * Code: 201 Created
    * Content: The created company
* Error response:
    * Code: 401 Unauthorized
    * Content: "Not authorized"

#### PUT

<strong>URI: `PUT` http://localhost:8000/api/companies/{id} </strong>

Function: Updates the company with the specified id.

Parameters:

|Parameter|Type  |Description        |Required|
|---------|------|-------------------|--------|
|`name`   |string|Name of the company|false   |

* Success response:
    * Code: 200 OK
    * Content: The updated company
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (company not found):
    * Code: 404 Not found
    * Content: "Sorry, company not found"
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error specification

#### DELETE

<strong>URI: `GET` http://localhost:8000/api/companies/{id} </strong>

Function: Deletes the company with the specified id. Only for users with role: 1.

* Success response:
    * Code: 200 OK
    * Content: "Company deleted successfully"
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (company not found):
    * Code: 404 Not found
    * Content: "Sorry, company not found"

### Gear
#### GET

<strong>URI: `GET` http://localhost:8000/api/gear/all </strong>

Function: Returns all gear. Only for users with role: 1.

Parameters:

|Parameter|Type  |Description |Required|
|---------|------|------------|--------|
|`search` |string|Search query|false   |

* Success response:
    * Code: 200 OK
    * Content: A list of all gear
* Error response:
    * Code: 401 Unauthorized
    * Content: "Not authorized"

<strong>URI: `GET` http://localhost:8000/api/gear </strong>

Function: Returns all user's gear

Parameters:

|Parameter|Type  |Description |Required|
|---------|------|------------|--------|
|`search` |string|Search query|false   |

* Success response:
    * Code: 200 OK
    * Content: A list of all user's gear

<strong>URI: `GET` http://localhost:8000/api/gear/user/{id} </strong>

Function: Returns all user's with the specified id gear. Only for users with role: 1.

* Success response:
    * Code: 200 OK
    * Content: A list of all user's gear
* Error response:
    * Code: 401 Unauthorized
    * Content: "Not authorized"

<strong>URI: `GET` http://localhost:8000/api/gear/all/{id} </strong>

Function: Returns the gear with the specified id. Only for users with role: 1.

* Success response:
    * Code: 200 OK
    * Content: The gear with the specified id
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (gear not found):
    * Code: 404 Not Found
    * Content: "Sorry, gear not found"

<strong>URI: `GET` http://localhost:8000/api/gear/{id} </strong>

Function: Returns the user's gear with the specified id

* Success response:
    * Code: 200 OK
    * Content: The gear with the specified id
* Error response:
    * Code: 404 Not Found
    * Content: "Sorry, gear not found"


#### POST

<strong>URI: `POST` http://localhost:8000/api/gear </strong>

Function: Creates a new gear.

Parameters:

|Parameter      |Type  |Description           |Required|
|---------------|------|----------------------|--------|
|`name`         |string|Gear's name           |true    |
|`code`         |string|Gear's code           |true    |
|`description`  |string|Gear's description    |true    |
|`serial_number`|string|Gear's serial number  |true    |
|`unit_price`   |double|Unit price of the gear|true    |
|`long_term`    |bool  |Is the gear long-term |true    |
|`user_id`      |int   |Gear's owner's id     |true    |
|`amount`       |int   |Quantity of the gear  |false   |

* Success response:
    * Code: 201 Created
    * Content: The created gear
* Error response:
    * Code: 400 Bad request
    * Content: Error specification

#### PUT

<strong>URI: `PUT` http://localhost:8000/api/gear/{id} </strong>

Function: Updates all the gear's data with the specified id.  Only for users with roles: 1.

Parameters:

|Parameter      |Type  |Description           |Required|
|---------------|------|----------------------|--------|
|`name`         |string|Gear's name           |false   |
|`code`         |string|Gear's code           |false    |
|`description`  |string|Gear's description    |false    |
|`serial_number`|string|Gear's serial number  |false   |
|`unit_price`   |double|Unit price of the gear|false   |
|`long_term`    |bool  |Is the gear long-term |false   |
|`lent`         |bool  |Is the gear lent      |false   |
|`user_id`      |int   |Gear's owner's id     |false   |

* Success response:
    * Code: 200 OK
    * Content: The updated gear
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (gear not found):
    * Code: 404 Not found
    * Content: "Sorry, gear not found"
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error specification

#### DELETE

<strong>URI: `GET` http://localhost:8000/api/gear/{id} </strong>

Function: Deletes the gear with the specified id. Only for users with role: 1.

* Success response:
    * Code: 200 OK
    * Content: "Gear deleted successfully"
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (gear not found):
    * Code: 404 Not found
    * Content: "Sorry, gear not found"

### Requests
#### GET

<strong>URI: `GET` http://localhost:8000/api/requests </strong>

Function: Returns all user's requests

* Success response:
    * Code: 200 OK
    * Content: A list of all user's requests

<strong>URI: `GET` http://localhost:8000/api/requests/pendingLends </strong>

Function: Returns all user's pending lends

* Success response:
    * Code: 200 OK
    * Content: A list of all user's pending lends

<strong>URI: `GET` http://localhost:8000/api/requests/{id} </strong>

Function: Returns the request with the specified id

* Success response:
    * Code: 200 OK
    * Content: The request with the specified id
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request not found"

#### POST

<strong>URI: `POST` http://localhost:8000/api/requests/lend/{id} </strong>

Function: Creates a request to lend gear (id in URI is the id of the gear to be lent)

Parameters:

|Parameter|Type  |Description                                  |Required|
|---------|------|---------------------------------------------|--------|
|`user_id`|int   |Id of the user that the gear is being lent to|true    |

* Success response:
    * Code: 200 OK
    * Content: "Lend request sent."
* Error response (gear not found):
    * Code: 404 Not found
    * Content: "Sorry, gear not found"
* Error response (user not found):
    * Code: 404 Not found
    * Content: "Sorry, user not found"
* Error response (gear already has a request):
    * Code: 400 Bad request
    * Content: "Gear already has a request"
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error specification

<strong>URI: `POST` http://localhost:8000/api/requests/acceptLend/{id} </strong>

Function: Accepts a lend request (id in URI is the id of the lend request)

* Success response:
    * Code: 200 OK
    * Content: "Lend request accepted."
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request not found"

<strong>URI: `POST` http://localhost:8000/api/requests/return/{id} </strong>

Function: Returns lent gear (id in URI is the id of the gear that is being returned)

* Success response:
    * Code: 200 OK
    * Content: "Return request created"
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request/gear not found"

<strong>URI: `POST` http://localhost:8000/api/requests/acceptReturn/{id} </strong>

Function: Accepts return request (id in URI is the id of the request that is being accepted)

* Success response:
    * Code: 200 OK
    * Content: "Gear returned"
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request/gear not found"

#### DELETE

<strong>URI: `DELETE` http://localhost:8000/api/requests/{id} </strong>

Function: Deletes the request with the specified id (if the request belongs to the user or user's gear).

* Success response:
    * Code: 200 OK
    * Content: "Request deleted successfully."
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request not found."

### Password reset
#### POST

<strong>URI: `POST` http://localhost:8000/api/reset-password </strong>

Function: Sends a password reset link to the specified email

|Parameter|Type  |Description                                        |Required|
|---------|------|---------------------------------------------------|--------|
|`email`  |string|The email of the user, who wants his password reset|true    |

* Success response:
    * Code: 200 OK
    * Content: "Password reset email has been sent."
* Error response:
    * Code: 404 Not found
    * Content: "Email does not exist."

<strong>URI: `POST` http://localhost:8000/api/change-password </strong>

Function: Changes user's password to a new one

|Parameter |Type  |Description                                              |Required|
|----------|------|---------------------------------------------------------|--------|
|`email`   |string|The email of the user, who wants his password reset      |true    |
|`token`   |string|Token, which is given with the link to reset/set password|true    |
|`password`|string|The new password                                         |true    |

* Success response:
    * Code: 201 Created
    * Content: "Password has been updated."
* Error response:
    * Code: 422 Unprocessable Content
    * Content: "Either your email or token is wrong."

### History
#### GET

<strong>URI: `GET` http://localhost:8000/api/history </strong>

Function: Returns user's history

Event parameter explanation: </br>
`0 = lent` `1 = returned` `2 = gave away` `3 = deleted`

* Success response:
    * Code: 200 OK
    * Content: User's history

<strong>URI: `GET` http://localhost:8000/api/gearHistory/{id} </strong>

Function: Returns gear's with specified id history

Event parameter explanation: </br>
`0 = lent` `1 = returned` `2 = deleted`

* Success response:
    * Code: 200 OK
    * Content: Gear's History

