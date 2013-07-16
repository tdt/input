# Vertere CSV2RDF Mapping Language

Vertere is a tool for converting data in CSV format into RDF. The Vertere
mapping language described in this document uses Turtle syntax, and is
designed so as to resemble to target RDF graph as much as possible. Another
design choice is to ensure that each line in the source CSV file can be
processed individually, ensuring that the conversion process can be
parallelised easily.

The Verter mapping language is based on RDF. This means that everything is
declared using triples, that consist of a _subject_, _predicate_ and an
_object_. Each part of a triple is a resource, which is identified by a unique
URI. Each URI typically has a namespace (e.g.,
`http://example.com/namespace/`), followed by the name of the resource (e.g.,
`Concept`). In Turtle, triples are declared using the following notation:

    
    <http://example.com/Subject> <http://example.com/predicate> <http://example.com/Object> .

This document assumes basic knowledge of RDF and the Turtle syntax. For more
information, visit the [W3C team submission on Turtle - Terse RDF Triple
Language](http://www.w3.org/TeamSubmission/turtle/)

## Vertere Vocabulary

All Vertere-specific instructions are defined in the Vertere vocabulary
namespace, which is introduced as the base namespace at the top of the
conversion document. The namespace is not yet formally defined, so the user is
free to fill in any URI they like.

    
    @prefix : <http://example.com/schema/data_conversion#> .

Next, the user is able to declare extra prefixes to vocabulary (or ontology)
namespaces. This allows using the qualified name later in the spec, instead of
having to write the full URI to a resource. For example, if we want to map a
CSV file to concepts in the [TRANSIT
vocabulary](http://vocab.org/transit/terms/), we can declare the prefix
`transit`. This way, we can use `transit:Stop` instead of having to write
`<http://vocab.org/transit/terms/Stop>`.

    
    @prefix transit: <http://vocab.org/transit/terms/> .

## Entry Point

The entry point to the mapping specification is a `:Spec` object (here
identified as the document itself), which gives basic information about the
data source. Such an object can be declared by stating that the root URI of
the type `:Spec` is.

    
    <#> a :Spec .

The spec resource expects the following metadata about the data source:

  * **Format**  
Supplies the format of the datafile that needs to be mapped. possible values
(for now) are `:CSV` or `:TSV`, depending on whether your mapping a file using
commas or tabs to separate the values.

    
    <#> :format :CSV .

  * **Header rows**  
Supplies a binary value that indicates if the file contains a header row (`1`)
or not (`0`).

    
    <#> :header_rows 0 .

  * **Expected header**  
When `:header_rows` is set to 1, you can supply the header row you expect in
the document.

    
    <#> :expected_header ( "\"id\",\"code\",\"local_code\",\"name\",\"continent\" ).

  * **Resource**  
With resource, you declare the resources that the mapping process will output.
The resources that are Each mapping for each resource that is defined here,

    
    <#> :resource <#customer>, <#email>, <#phone>, <#affiliation>, <#sales_person> .

  * **Base URI**  
The base URI define the namespace where the created resources will be based
under.

    
    <#> :base_uri "<http://my.data.source/test>" ;

Turtle allows a shorter notation when several triples share the same subject.
Declaring all above triples can be written like such:

    
    
    <#> a :Spec;
      :format :CSV;
      :header_rows 1;
      :expected_header ( "\"id\",\"code\",\"local_code\",\"name\",\"continent\",\"iso_country\",\"wikipedia_link\",\"keywords\"" );
      :resource <#continent>, <#country>, <#region>, <#ourairports_region_page>, <#wikipedia_page>, <#dbpedia_resource>;
      :base_uri "http://data.kasabi.com/dataset/world_geography/".
        

# Declaring mappings

## Declaring and identifying resources

RDF uses unique URIs to identify resources. This means that these URIs need to
be created from the dataset. This is done by declaring the resources and
adding `:identify` property.

Declaring a resource works the same way as creating the `:Spec` object. This
time, we choose a variable name, for example _Continent_, and say it is a
`:Resource`.

    
    
    <#continent> a :Resource .

Next, we specify how the identifying URI should be created. The value of
`:identify` is a _blank node_, which is a resource that does not really exists
(and has no URI), and is added in Turtle between `[]`. Between these brackets,
you can add predicates and objects as you would with any other resource. We
use two predicates with our blank node:

  * **Source column**  
With source column, we define from which column, the values will be used to
create the unique URI. This is typically a column with IDs (or primary keys),
where the values are surely unique. For this, the predicate `:source_column`
is used, the value is an _integer_, representing the column index (starting
from 1).

  * **Base URI**  
Sometimes we want to use collections in our URIs. You can specify a different
base URI using `:base_uri`, the value is a _string_.

This example will, for example, create the uri _http://data.kasabi.com/dataset
/world-geography/continents/Europe_ if a value in the fifth column would be
"Europe".

    
    
    <#continent> a :Resource;
      :identity [
        :source_column 5;
        :base_uri "http://data.kasabi.com/dataset/world-geography/continents/"
      ] .
        

In some case, columns will need to be combined in order to obtain a unique
value (e.g., firstname and lastname). With the predicate `:source_columns` you
can refer to the indexes of the columns you want to combine. The value are two
or more integers between brackets, separated by a space. By adding
`:source_columns_glue`, you can define a string that will be used to glue the
pieces together (e.g., underscore). A slash `/` is used to create a deeper
hierarchy in the URI.

    
    
    <#ourairports_region_page> a :Resource;
      :type bibo:Webpage;
      :identity [
           :source_columns ( 6 3 ) ;
    	:source_column_glue "/" ;
    	:base_uri "http://www.ourairports.com/countries/"
      ].

### Using URI templates

Besides simple concatenation, the URI might be constructed in more complex
manner. For that reason, are URI templates supported. URI templates provide a
flexible way to specify a URI using variables, and asign values afterwards.
The spec can be found [here](http://tools.ietf.org/html/rfc6570).

A URI template is added to value of `:identify`, by using the following
predicates:

  * **Template**  
You can specify a template using `:template`, the value is a _string_.

  * **Template variables**  
The variables that need to fill the variables used in the template, are added
using `:template_vars`. The value is a list of blank nodes, each specifying a
variable name and a source column:

    * **Variable name**  
This value represents the name of the variable used in the template. For this,
the predicate `:variable` is used.

    * **Source column**  
With source column, we define from which column, the values will be used to
create the unique URI. This is typically a column with IDs (or primary keys),
where the values are surely unique. For this, the predicate `:source_column`
is used, the value is an _integer_, representing the column index (starting
from 1).

The above example can also be written as:

    
    
    <#ourairports_region_page> a :Resource;
      :type bibo:Webpage;
      :identity [
        :template "http://www.ourairports.com/countries/{country_id}/{local_id}";
        :template_vars [
          :variable "country_id";
          :source_column 6
        ],[
          :variable "local_id";
          :source_column 3
        ]
      ].

## Typing resources

Typing is the most basic operation in mapping. In the output RDF, it will
create triples using the predicate `rdf:type` (or in Turtle the shorthand
`a`). The object of this triple, will typically be a class, defined in the
ontology that we are mapping to. The created resource, will therefore be an
instance of this type. In this mapping language, we type a resource by using
the `:type` predicate, followed by the URI of the concept.

    
    <#wikipedia_page> :type bibo:Webpage .

In this example, `bibo` represents the [Bibliographic Ontology
Specification](http://purl.org/ontology/bibo/) namespace. Therefore,
`bibo:Webpage` can also be written as
`<http://purl.org/ontology/bibo/Webpage>` .

## Adding attributes and relations to resources

Besides typing, you might want to add some related data to your resource. In
RDF (or typically in [Linked Data](http://linkeddata.org/)), this is done in
two ways. They both use a blank node as object.

Firstly, by adding _links_ to other resources, created from your data set or
already existing on the Web. In this mapping language defined with the
`:relationship` predicate. The object, a blank node, typically has two
predicates:

  * The `:property` predicate, which refers to the property in the ontology you want to use.
  * The `:object_from` predicate, which refers to another resource, declared elsewhere in the document. 
    
    
    <#region> :relationship [
      :property owl:sameAs;
      :object_from <#dbpedia_resource>
    ] .
    
    <http://test.com/REGION11> owl:sameAs <http://dbpedia.org/resource/Aix-en-Provence> .

Secondly, by adding triples with _literals_ as object value. In many cases,
the values of a column in your CSV file just need to be added as a string or
integer. In this language defined with the `:attribute` predicate. The object,
a blank node, typically has these predicates:

  * The `:property` predicate, which refers to the property in the ontology you want to use to add the literal value.
  * The `:source_column` predicate, which refers to a column using its index. 
  * The `:language` predicate, which can add a language code to a string value.
  * The `:datatype` predicate, which adds a XSD datatype to the value

Mapping  Output example

    
    
    <#region> [
      :property geo:alt;
      :source_column 7;
      :datatype xsd:float;
    ] .
    
    <http://test.com/REGION11> geo:alt "3.14"^^xsd:float .
    
    
    <#region> [
      :property rdfs:label;
      :source_column 7;
      :language "en";
    ] .
    
    <http://test.com/REGION11> rdfs:label "Provence"@en .

A fully mapped resource typically consists of typing, identity, relations and
attributes, as shown in the combined example below:

    
    
    <#region> a :Resource;
      :type places:Region;
      :identity [
    	:source_column 2;
    	:base_uri "http://data.kasabi.com/dataset/world-geography/regions/"
      ];
      :relationship [
        :property owl:sameAs;
        :object_from <#dbpedia_resource>
      ],
      [
        :property foaf:isPrimaryTopicOf;
        :object_from <#wikipedia_page>
      ],
      :attribute [
        :property fly:iso_code;
        :source_column 2
      ],
      [
        :property foaf:name;
        :source_column 4
      ].

## Processing column values

Column values can be processed before using them. This is done by calling a
process function, which is specified by adding the predicate `:process`. Its
object is an ordered list of defined functions which will be executed
sequentially. We discuss some of them in detail.

### Using conversions

Conversions transform a value into another value and are specified as custom
PHP functions. These function are specified in the class `Conversions` located
at _custom/conversions.class.php_.

    
    
    <?php
    /*
     * Class for custom conversion methods
     */
    class Conversions {
        /*
         * Converts a value from feet to metres
         */
    	public static function feet_to_metres($value) {
    		return ($value * 0.3048);
    	}
    
    	public static function metres_to_feet($value) {
    		return ($value * 3.2808);
    	}
    
    }
    ?>
        

This class can be extended with extra functions. In the mapping file, a
function is called by adding `:functionname` to the object list. In the
following example, we add the altitude of a region. The values in the data
columns are in feet, so we use a process function to convert them.

    
    
    <#region> a :Resource;
    
    :attribute [
      :property geo:alt;
      :source_column 7;
      :datatype xsd:float;
      :process (:feet_to_metres)
    ] .
        

### Using regular expressions

With the Vertere mapping language, you can also specify regular expression
patterns to transform the values in certain columns.

In this example, we transform the Wikipedia URIs, defined in another resource
`<#wikipedia_page>`, to DBpedia URIs. We can use this `<#dbpedia_resource>`
elsewhere as an object. (e.g., to create _sameAs_ links)

    
    
    <#dbpedia_resource> a :Resource; 
      :identity [
        :source_resource <#wikipedia_page>;
        :base_uri "" ;
        :process ( :regex );
        :regex_match "http://[^/]*/wiki/(.*)";
        :regex_output "http://dbpedia.org/resource/${1}";
      ] .

## Using lookup

## Transforming values with process functions

## Combining techniques

Many of the above techniques can be combined.

# Example

The following example maps a CSV file with airports using the TRANSIT, PLACES,
GEO, GEORSS, NAPTAN, FOAF, FLY, SPACEREL and BIBO volcabulary.

    
    
    "id","ident","type","name","latitude_deg","longitude_deg","elevation_ft","continent","iso_country","iso_region","municipality","scheduled_service","gps_code","iata_code","local_code","home_link","wikipedia_link","keywords"
    6523,"00A","heliport","Total Rf Heliport",40.07080078125,-74.9336013793945,11,"NA","US","US-PA","Bensalem","no","00A",,"00A",,,
    6524,"00AK","small_airport","Lowell Field",59.94919968,-151.695999146,450,"NA","US","US-AK","Anchor Point","no","00AK",,"00AK",,,
    6525,"00AL","small_airport","Epps Airpark",34.8647994995117,-86.7703018188477,820,"NA","US","US-AL","Harvest","no","00AL",,"00AL",,,
    6526,"00AR","heliport","Newport Hospital & Clinic Heliport",35.608699798584,-91.2548980712891,237,"NA","US","US-AR","Newport","no","00AR",,"00AR",,,
    6527,"00AZ","small_airport","Cordes Airport",34.3055992126465,-112.165000915527,3810,"NA","US","US-AZ","Cordes","no","00AZ",,"00AZ",,,
    6528,"00CA","small_airport","Goldstone /Gts/ Airport",35.3504981995,-116.888000488,3038,"NA","US","US-CA","Barstow","no","00CA",,"00CA",,,
    6529,"00CO","small_airport","Cass Field",40.622200012207,-104.34400177002,4830,"NA","US","US-CO","Briggsdale","no","00CO",,"00CO",,,
    6531,"00FA","small_airport","Grass Patch Airport",28.6455001831055,-82.2190017700195,53,"NA","US","US-FL","Bushnell","no","00FA",,"00FA",,,
    6532,"00FD","heliport","Ringhaver Heliport",28.8465995788574,-82.3453979492188,25,"NA","US","US-FL","Riverview","no","00FD",,"00FD",,,
        
    
    
    @prefix : <http://example.com/schema/data_conversion#> .
    @prefix bibo: <http://purl.org/ontology/bibo/> .
    @prefix fly: <http://vocab.org/fly/schema/> .
    @prefix foaf: <http://xmlns.com/foaf/0.1/> .
    @prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> .
    @prefix georss: <http://www.georss.org/georss/> .
    @prefix naptan: <http://transport.data.gov.uk/def/naptan/> .
    @prefix owl: <http://www.w3.org/2002/07/owl#> .
    @prefix places: <http://purl.org/ontology/places#> .
    @prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
    @prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
    @prefix spacerel: <http://data.ordnancesurvey.co.uk/ontology/spatialrelations/> .
    @prefix transit: <http://vocab.org/transit/terms/> .
    @prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
    
    # 1    id
    # 2    ident
    # 3    type
    # 4    name
    # 5    latitude_deg
    # 6    longitude_deg
    # 7    elevation_ft
    # 8    continent
    # 9    iso_country
    # 10   iso_region
    # 11   municipality
    # 12   scheduled_service
    # 13   gps_code
    # 14   iata_code
    # 15   local_code
    # 16   home_link
    # 17   wikipedia_link
    # 18   keywords
    
    <#> a :Spec;
    :format :CSV;
    :header_rows 1;
    :expected_header ( "\"id\",\"ident\",\"type\",\"name\",\"latitude_deg\",\"longitude_deg\",\"elevation_ft\",\"continent\",\"iso_country\",\"iso_region\",\"municipality\",\"scheduled_service\",\"gps_code\",\"iata_code\",\"local_code\",\"home_link\",\"wikipedia_link\",\"keywords\"" );
    :resource <#airport>, <#airport_type>, <#country>, <#continent>, <#region>, <#municipality>, <#wikipedia_page>, <#dbpedia_resource>, <#ourairports_page>, <#naptan_resource>;
    :base_uri "http://data.kasabi.com/dataset/airports/" .
    
    <#airport> a :Resource
    ; :identity [ :source_column 2; ]
    ; :type fly:Airport, transit:Stop, naptan:Airport
    ; :relationship
    	[ :property rdf:type; :object_from <#airport_type> ],
    	[ :property spacerel:within; :object_from <#municipality> ],
    	[ :property spacerel:within; :object_from <#region> ],
    	[ :property spacerel:within; :object_from <#country> ],
    	[ :property spacerel:within; :object_from <#continent> ],
    	[ :property foaf:isPrimaryTopicOf; :object_from <#wikipedia_page> ],
    	[ :property foaf:isPrimaryTopicOf; :object_from <#ourairports_page> ],
    	[ :property owl:sameAs; :object_from <#dbpedia_resource> ],
    	[ :property owl:sameAs; :object_from <#naptan_resource> ]
    ; :attribute
    	[ :property geo:lat; :source_column 5; :datatype xsd:float ],
    	[ :property geo:long; :source_column 6; :datatype xsd:float ],
    	[ :property geo:alt; :source_column 7; :datatype xsd:float; :process ( :feet_to_metres ); ],
    	[ :property georss:point; :source_columns (5 6); :source_column_glue " " ],
    	[ :property foaf:name; :source_column 4; :language "en" ],
    	[ :property fly:icao_code; :source_column 2 ],
    	[ :property fly:scheduled_service; :source_column 12; :lookup <#boolean_lookup> ]
    .
    
    <#airport_type> a :Resource
    ; :identity [ :source_column 3; :process ( :normalise :title_case ); :base_uri "http://data.kasabi.com/dataset/airports/schema/" ]
    ; :type rdfs:Class
    ; :attribute
    	[
    		:property rdfs:label;
    		:source_column 3;
    		:process ( :regex :title_case );
    		:regex_match "_";
    		:regex_output " ";
    	]
    .
    
    <#continent> a :Resource
    ; :identity [
    	:source_column 8;
    	:base_uri "http://data.kasabi.com/dataset/world-geography/continents/"
    ]
    .
    
    <#country> a :Resource;
        :identity [
            :source_column 9;
            :base_uri "http://data.kasabi.com/dataset/world-geography/countries/"
        ] .
    
    <#region> a :Resource;
        :type places:Region;
        :identity [
            :source_column 10;
            :base_uri "http://data.kasabi.com/dataset/world-geography/regions/"
    	];
        :relationship [
            :property spacerel:contains;
            :object_from <#municipality>
        ] .
    
    <#municipality> a :Resource;
        :type places:Municipality;
        :identity [
            :source_column 11;
            :container "municipalities";
            :process ( :flatten_utf8 :normalise );
    	];
        :relationship [
            :property spacerel:within;
            :object_from <#region>
        ];
        :attribute [
            :property rdfs:label;
            :source_column 11
        ] .
    
    <#wikipedia_page> a :Resource;
        :type bibo:Webpage;
        :identity [
            :source_column 17;
            :base_uri "";
            :process ( :regex );
            :regex_match " ";
            :regex_output "";
    	 ] .
    
    <#dbpedia_resource> a :Resource;
        :identity [
            :source_resource <#wikipedia_page>;
            :process ( :regex );
            :regex_match "http://[^/]*/wiki/(.*)";
            :regex_output "http://dbpedia.org/resource/${1}";
    	] .
    
    <#ourairports_page> a :Resource;
        :type bibo:Webpage;
        :identity [
            :source_column 2;
            :base_uri "";
            :process ( :regex );
            :regex_match "^(.*)$";
            :regex_output "http://www.ourairports.com/airports/${1}/";
    	] .
    
    <#naptan_resource> a :Resource; 
      :identity [
    	:source_column 14;
    	:base_uri "http://transport.data.gov.uk/id/airport/"
      ].
    
    <#boolean_lookup> a :Lookup; 
      :lookup_entry [ 
        :lookup_key "yes"; 
        :lookup_value "true"^^xsd:boolean 
      ]; 
      :lookup_entry [ 
        :lookup_key "true"; 
        :lookup_value "true"^^xsd:boolean 
      ]; 
      :lookup_entry [ 
        :lookup_key "no"; 
        :lookup_value "false"^^xsd:boolean 
      ]; 
      :lookup_entry [ 
        :lookup_key "false"; 
        :lookup_value "false"^^xsd:boolean 
      ] .
    
        
