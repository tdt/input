#/bin/bash

curl http://localhost/start/public/tdtinput/airports/airport_json -XPUT -d '{
	"name": "Airports", 
	"occurence": 1,
	"extract":{
		"type":"JSON",
		"source":"http://localhost/files/airport.json"
	},
	"map": {
		"type": "RDF",
		"mapfile": "http://localhost/files/airport.spec.ttl",
		"datatank_package": "airports",
		"datatank_resource": "airport_json",
		"datatank_uri": "http://localhost/start/public/"},
	"load" : {
		"type":"CLI"
		}
}' -i -u jobadmin -p
