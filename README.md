moodle-local_brookesid_ws
=========================

A Moodle plugin that provides a web service to enable the posting and retrieval of data for the 'Brookes ID' app.

Users must authenticate by sending a GET or POST request to moodle_base_url/login/token.php, passing the parameters username, password and service which should be set to 'brookesid-ws'. A token will be returned if successfully authenticated. This token must be passed with each request to the web service.

Web service function calls should be POSTed to moodle_base_url/rest/server.php, passing the parameters moodlewsrestformat set to 'json', wstoken set to the value of the previously obtained token, wsfunction set to the name of the function to call, and any other parameters required by the function.

<h2>INSTALLATION</h2>
This plugin should be installed in the local directory of the Moodle instance.
