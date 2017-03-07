LibreCores User Management
==========================

Account Creation
----------------

Local Account
~~~~~~~~~~~~~

Local account creation follows the usual procedure, known from online
services.

Prerequisite: The user is not logged in on the LibreCores site.

1) The new user enters username, email address and password
2) Account checking

  a) If username and email address are not in the database yet, a new user account is created.
  b) If username or email address already exist in the database, the new user nees to pick other values, or log in with his/her existing account.

Final state: The user is either logged in with the new account (account
creation successful); or remains not logged in (account creation
failed).

OAuth Account (GitHub/Google)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

OAuth-based account creation avoids that the user needs to enter his/her
user data again if he/she has already registered with a OAuth account
provider (currently supported: GitHub and Google). In addition, the user
can log in with through the OAuth provider, instead of entering the user
credentials on the LibreCores site.

Prerequisite: The user is not logged in on the LibreCores site.

1) The new user clicks on the OAuth provider logo.
2) The new user is redirected to the OAuth provider, where he/she logs
   in and/or confirms the data transmission to LibreCores. The exact
   steps depend on the OAuth provider itself.
3) The user is redirected back from the OAuth provider to the LibreCores
   site with a OAuth token.
4) The LibreCores site uses the received OAuth token to request user
   data from the OAuth provider. Requested user data: email address and
   the username used on the OAuth service.
5) If the OAuth response does not contain an username or an email
   address, the account creation fails with an error message.
6) The LibreCores user database is searched for an existing account
   associated with the username or the email address.
7) The OAuth provided username and email address are used to create a new user
   account.
8) User validation

  a) If the newly created user does not validate, e.g. because the username
     provided by the OAuth provider does not follow our rules or is already
     taken, the user is redirected to the registration form.
     The registration form is pre-filled with the data used for the automatic
     account creation and left for the user to modify.
     After submitting the registration form successfully, the newly created
     account is automatically connected to the OAuth account provided in the
     first place.
  b) If no account with either the email address or
     the username exists, a new LibreCores user account is created. The
     email address and the username are filled with the data from the
     OAuth provider. The username received from the OAuth provider,
     together with the OAuth access token, are stored in the user object.

Final state: The user is either logged in with the new account (account
creation successful); or remains not logged in (account creation
failed).

Login
-----

Local Login
~~~~~~~~~~~
The username or e-mail address is checked against the local database.

OAuth Login
~~~~~~~~~~~
A button "Login with SERVICE" is provided, which redirects the user to the auth provider.
After a successful login, the auth provider redirects the user back to the previously viewed page on LibreCores.
If no user account is associated with the OAuth account, the account is automatically created (see "Account Creation" above).


Connecting an Existing Account with an OAuth Provider
-----------------------------------------------------

Users can connect (associate) an existing account on LibreCores with an OAuth-provider.
Multiple OAuth accounts can be connected to a single LibreCores account.
Every OAuth account can be connected only to one LibreCores account.
