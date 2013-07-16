curl http://localhost/test/public/tdtinput/airports/airports_xml -XPUT -d '{
	"name": "Airports", 
	"occurence": 1,
	"extract":{
		"type":"XML",
		"arraylevel":"3",
		"source":"http://localhost/files/airport.xml"
	},
	"map": {
		"type": "RDF",
		"mapfile": "http://localhost/files/airport.xml.spec.ttl",
		"datatank_package": "airports",
		"datatank_resource": "airport_xml",
		"datatank_uri": "http://localhost/test/public/"},
	"load" : {
		"type":"CLI"
		}
}' -i -u jobadmin -p
