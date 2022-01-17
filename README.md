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
  * [POST](#post-1)
- [Company](#company)
  * [GET](#get-1)
  * [POST](#post-2)
  * [PUT](#put-1)
  * [DELETE](#delete-1)
- [Gear](#gear)
  * [GET](#get-2)
  * [POST](#post-3)
  * [PUT](#put-2)
  * [DELETE](#delete-2)
- [Requests](#requests)
  * [GET](#get-3)
  * [POST](#post-4)
  * [DELETE](#delete-3)
- [Password reset](#password-reset)
  * [POST](#post-5)
- [History](#history)
  * [GET](#get-4)

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
* Error response:
    * Code: 401 Unauthorized
    * Content: Unauthorized

<strong>URI: `GET` http://localhost:8000/api/users </strong>

Function: Returns all users within the user company

Parameters:

|Parameter|Type  |Description |Required|
|---------|------|------------|--------|
|`search` |string|Search query|false   |

* Success response:
    * Code: 200 OK
    * Content: a list of all users

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

|Parameter              |Type  |Description                        |Required|
|-----------------------|------|-----------------------------------|--------|
|`first_name`           |string|The first name of the user         |true    |
|`last_name`            |string|The last name of the user          |true    |
|`email`                |string|Email of the user. Has to be unique|true    |
|`company_id`           |int   |User's company id                  |true    |
|`role`                 |int   |User's role (0: regular, 1: admin) |true    |

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

|Parameter              |Type  |Description                        |Required|
|-----------------------|------|-----------------------------------|--------|
|`first_name`           |string|The first name of the user         |false   |
|`last_name`            |string|The last name of the user          |false   |
|`email`                |string|Email of the user. Has to be unique|false   |
|`role`                 |int   |User's role (0: regular, 1: admin) |false   |

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

Function: Deletes the user with the specified id. Only for users with role: 1.

* Success response:
    * Code: 200 OK
    * Content: "User deleted successfully"
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (user not found):
    * Code: 404 Not found
    * Content: "Sorry, user not found"
* Error response (user has gear):
    * Code: 400 Bad request
    * Content: "User cannot be deleted, because user has gear"

### Authorization
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
    * Content: "Not authorized"

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
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error specification

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
* Error response (company has users):
    * Code: 400 Company still has users"

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
* Error response (unauthorized):
    * Code: 401 Unauthorized
    * Content: "Not authorized"
* Error response (user not found):
    * Code: 404 Not Found
    * Content: "Sorry, user not found"

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

<strong>URI: `GET` http://localhost:8000/api/gear/code/{code} </strong>

Function: Returns gear with the specified code

* Success response:
    * Code: 200 OK
    * Content: The gear with the specified code
* Error response:
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

<strong>URI: `GET` http://localhost:8000/api/gear/pdf/{id} </strong>

Function: Generates a pdf of gear with the selected id.

* Success response:
    * Code: 200 OK
    * Content: The generated pdf
* Error response (if you're not an admin and selected gear that isn't yours):
    * Code: 401 Unauthorized
    * Content: "Not authorized"

#### POST

<strong>URI: `POST` http://localhost:8000/api/gear </strong>

Function: Creates a new gear.

Parameters:

|Parameter      |Type  |Description                        |Required|
|---------------|------|-----------------------------------|--------|
|`name`         |string|Gear's name                        |true    |
|`code`         |string|Gear's code                        |true    |
|`description`  |string|Gear's description. Max length: 255|true    |
|`serial_number`|string|Gear's serial number. Unique       |true    |
|`unit_price`   |double|Unit price of the gear             |true    |
|`long_term`    |bool  |Is the gear long-term              |true    |
|`user_id`      |int   |Gear's owner's id                  |true    |
|`amount`       |int   |Quantity of the gear. Max: 50      |true    |

* Success response:
    * Code: 201 Created
    * Content: The created gear
* Error response:
    * Code: 400 Bad request
    * Content: Error specification

#### PUT

<strong>URI: `PUT` http://localhost:8000/api/gear/{id} </strong>

Function: Updates all the gear's data with the specified id.

Parameters:

|Parameter      |Type  |Description                        |Required|
|---------------|------|-----------------------------------|--------|
|`name`         |string|Gear's name                        |false   |
|`code`         |string|Gear's code                        |false   |
|`description`  |string|Gear's description. Max length: 255|false   |
|`serial_number`|string|Gear's serial number. Unique       |false   |
|`unit_price`   |double|Unit price of the gear             |false   |
|`long_term`    |bool  |Is the gear long-term              |false   |
|`user_id`      |int   |Gear's owner's id                  |false   |

* Success response:
    * Code: 200 OK
    * Content: The updated gear
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
* Error response (gear is lent):
    * Code: 400 Bad request
    * Content: "You cannot delete lent gear"
* Error response (gear has a request):
    * Code: 400 Bad request
    * Content: "Gear has a request"

### Requests
#### GET

Status reference: `0 = pending lend` `1 = lent` `2 = pending return` `3 = pending giveaway`

<strong>URI: `GET` http://localhost:8000/api/requests/pending </strong>

Function: Returns all user's pending requests

* Success response:
    * Code: 200 OK
    * Content: A list of all user's pending requests

#### POST

<strong>URI: `POST` http://localhost:8000/api/requests/lend </strong>

Function: Creates a request to lend gear

Parameters:

|Parameter|Type     |Description                                                |Required|
|---------|---------|-----------------------------------------------------------|--------|
|`user_id`|int      |Id of the user that the gear is being lent to. Has to exist|true    |
|`gear_id`|int array|Array of gear, that is being lent, ids                     |true    |

* Success response:
    * Code: 200 OK
    * Content: "Lend request sent."
* Error response (gear not found):
    * Code: 404 Not found
    * Content: "Sorry, gear not found"
* Error response (lending to yourself):
    * Code: 400 Bad request
    * Content: "You cannot lend gear to yourself."
* Error response (user owns the gear):
    * Code: 400 Bad request
    * Content: "This user owns this gear."
* Error response (not holding the gear):
    * Code: 400 Bad request
    * Content: "You do not currently hold this gear."
* Error response (gear already has a request):
    * Code: 400 Bad request
    * Content: "Gear already has a request"
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error specification

<strong>URI: `POST` http://localhost:8000/api/requests/accept-lend/{id} </strong>

Function: Accepts a lend request (id in URI is the id of the lend request)

* Success response:
    * Code: 200 OK
    * Content: "Lend request accepted."
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request not found"

<strong>URI: `POST` http://localhost:8000/api/requests/return </strong>

Function: Returns lent gear

Parameters:

|Parameter|Type     |Description                               |Required|
|---------|---------|------------------------------------------|--------|
|`gear_id`|int array|Array of gear, that is being returned, ids|true    |

* Success response:
    * Code: 200 OK
    * Content: "Return request created"
* Error response (not found):
    * Code: 404 Not found
    * Content: "Sorry, request/gear not found"
* Error response (request already sent):
    * Code: 400 Bad request
    * Content: "Return request is already sent."
* Error response (gear is not in lent stage):
    * Code: 400 Bad request
    * Content: "Gear is not in lent stage."
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error message

<strong>URI: `POST` http://localhost:8000/api/requests/accept-return/{id} </strong>

Function: Accepts return request (id in URI is the id of the request that is being accepted)

* Success response:
    * Code: 200 OK
    * Content: "Gear returned"
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request/gear not found"

<strong>URI: `POST` http://localhost:8000/api/requests/decline-return/{id} </strong>

Function: Declines return request (id in URI is the id of the request that is being accepted)

* Success response:
    * Code: 200 OK
    * Content: "Return declined"
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request/gear not found"

<strong>URI: `POST` http://localhost:8000/api/requests/giveaway </strong>

Function: Gives away gear

Parameters:

|Parameter|Type     |Description                                                      |Required|
|---------|---------|-----------------------------------------------------------------|--------|
|`user_id`|int      |Id of the user that the gear is being given away to. Has to exist|true    |
|`gear_id`|int array|Array of gear, that is being given away, ids                     |true    |

* Success response:
    * Code: 200 OK
    * Content: "Giveaway request created"
* Error response (not found):
    * Code: 404 Not found
    * Content: "Sorry, gear not found"
* Error response (gear is lent):
    * Code: 400 Bad request
    * Content: "You cannot give away lent gear."
* Error response (gear already has a request):
    * Code: 400 Bad request
    * Content: "Gear already has a request"
* Error response (trying to giveaway to yourself):
    * Code: 400 Bad request
    * Content: "You cannot giveaway gear to yourself."
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error message

<strong>URI: `POST` http://localhost:8000/api/requests/accept-giveaway/{id} </strong>

Function: Accepts giveaway request (id in URI is the id of the request that is being accepted)

* Success response:
    * Code: 200 OK
    * Content: "Giveaway request accepted"
* Error response:
    * Code: 404 Not found
    * Content: "Sorry, request/gear not found"

<strong>URI: `POST` http://localhost:8000/api/requests/give-yourself </strong>

Function: Give yourself any gear. Only for users with role: 1.

Parameters:

|Parameter|Type     |Description                            |Required|
|---------|---------|---------------------------------------|--------|
|`gear_id`|int array|Array of gear, that is being taken, ids|true    |

* Success response:
    * Code: 200 OK
    * Content: "Giveaway request accepted"
* Error response (not found):
    * Code: 404 Not found
    * Content: "Sorry, gear not found"
* Error response (gear is lent):
    * Code: 400 Bad request
    * Content: "You cannot give away lent gear."
* Error response (gear already has a request):
    * Code: 400 Bad request
    * Content: "Gear already has a request"
* Error response (already have the gear):
    * Code: 400 Bad request
    * Content: "You already own that gear."
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error message

#### DELETE

<strong>URI: `DELETE` http://localhost:8000/api/requests/{id} </strong>

Function: Deletes the request with the specified id (if the request belongs to the user or user's gear).

* Success response:
    * Code: 200 OK
    * Content: "Request deleted successfully."
* Error response (status = 1 or 2):
    * Code: 400 Bad request
    * Content: "Cannot delete this request."
* Error response (not found):
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

|Parameter         |Type  |Description               |Required|
|------------------|------|--------------------------|--------|
|`password`        |string|The new password. Min: 6  |true    |
|`confirm_password`|string|The same password repeated|true    |

* Success response:
    * Code: 200 OK
    * Content: "Password changed successfully"
* Error response (passwords do not match):
    * Code: 400 Bad request
    * Content: "Passwords do not match"
* Error response (bad parameters):
    * Code: 400 Bad request
    * Content: Error message

### History
#### GET

Event parameter explanation: </br>
`0 = lent` `1 = returned` `2 = gave away` `3 = deleted`

<strong>URI: `GET` http://localhost:8000/api/history </strong>

Function: Returns user's history

* Success response:
    * Code: 200 OK
    * Content: User's history

<strong>URI: `GET` http://localhost:8000/api/gear-history/{id} </strong>

Function: Returns gear's with specified id history

* Success response:
    * Code: 200 OK
    * Content: Gear's History

