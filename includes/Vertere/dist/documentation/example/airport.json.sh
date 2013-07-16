#/bin/bash

curl http://localhost/test/public/tdtinput/airports/airport_json -XPUT -d '{
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
		"datatank_uri": "http://localhost/test/public/"},
	"load" : {
		"type":"CLI"
		}
}' -i -u jobadmin -p
