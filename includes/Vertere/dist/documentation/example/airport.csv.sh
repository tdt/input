curl http://localhost/start/public/tdtinput/airports/airports_csv -XPUT -d '{
	"name": "Airports", 
	"occurence": 1,
	"extract":{
		"type":"CSV",
		"delimiter":",",
		"has_header_row" : "1",
		"type":"CSV",
		"source":"http://localhost/files/airport.csv"
	},
	"map": {
		"type": "RDF",
		"mapfile": "http://localhost/files/airport.spec.ttl",
		"datatank_package": "airports",
		"datatank_resource": "airport_csv",
		"datatank_uri": "http://localhost/start/public/"},
		"load" : {"type":"CLI"}
}' -i -u jobadmin -p
