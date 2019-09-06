/*
* @title Brapci Harvesting OAI-PMH
* @author Rene Faustino Gabriel junior
* @version 0.19.03.31
*
* Módules NMP
* node.js>npm install --save sequelize
* node.js>npm install --save mysql2
* node.js>npm install yyyy-mm-dd
* node.js>npm install xml2js
* node.js>npm install xml2json
* npm install --save-dev -g nodemon
* node.js>npm install cheerio
* node.js>npm install request
*/

/************************************************************************/
/* Modules **************************************************************/
const express = require('express');
const router = require('./config/route');
//const db = require('./config/database');
const app = express();
//const source = require("./model/sources");

/* Routes **************************************************************/
app.use('/', router);
console.log('Welcome to Robot #01 - OAI Brapci - v'+version());
console.log("Started Robot");

/* Create Server ******************************************************/
var port = 8081;
app.listen(port, function() {
	console.log("Started server - version " + version());
	console.log("Port "+port);
	dt = Date();
	dt = dt.toLocaleString();
	console.log(dt);
});

function version() {
	return ("0.19.04.10");
}