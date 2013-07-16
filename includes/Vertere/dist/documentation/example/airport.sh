curl http://localhost/start/public/tdtinput/examples/airports -XPUT -d '{
	"name": "Airports", 
	"occurence": 1,
	"extract":{
		"type":"CSV",
		"source":"http://localhost/files/airport.csv"
	},
	"map": {
		"type": "RDF",
		"mapfile": "http://localhost/files/airport.spec.ttl",
		"datatank_package": "examples",
		"datatank_resource": "airports",
		"datatank_uri": "http://localhost/start/public/"},
		"load" : {"type":"CLI"}
}' -i 
