# Vertere Mapping Language

Vertere was a tool for converting data in CSV format into RDF.
    In the frame of DataTank (tdt/input), the Vertere mapping language
      was extended to map data in CSV, XML and JSON format into RDF.
      The Vertere mapping language described in this document uses
      Turtle syntax, and is designed so as to resemble to target RDF
      graph as much as possible. Another design choice is to ensure that
      each line in the source CSV file can be processed individually,
      ensuring that the conversion process can be parallelised easily.

The Verter mapping language is based on RDF.
    This means that everything is declared using triples,
    that consist of a _subject_, _predicate_ and an _object_.
    Each part of a triple is a resource, which is identified by a unique URI.
    Each URI typically has a namespace (e.g., `http://example.com/namespace/`),
    followed by the name of the resource (e.g., `Concept`).
      In Turtle, triples are declared using the following notation:

    <http://example.com/Subject> <http://example.com/predicate> <http://example.com/Object> .

This document assumes basic knowledge of RDF and the Turtle syntax. For more information, visit the [W3C team submission on Turtle - Terse RDF Triple Language](http://www.w3.org/TeamSubmission/turtle/)

## Vertere Vocabulary

All Vertere-specific instructions are defined in the Vertere vocabulary
      namespace, which is introduced as the base namespace at the top of the conversion document.
      The namespace is not yet formally defined, so the user is free to fill in any URI they like.

    @prefix : <http://example.com/schema/data_conversion#> .

Next, the user is able to declare extra prefixes to vocabulary (or ontology) namespaces.
      This allows using the qualified name later in the spec, instead of having to write the full URI to a resource.
      For example, if we want to map a CSV file to concepts in the [TRANSIT vocabulary](http://vocab.org/transit/terms/),
      we can declare the prefix `transit`. This way, we can use `transit:Stop` instead of having to write `<http://vocab.org/transit/terms/Stop>`.

    @prefix transit: <http://vocab.org/transit/terms/> .

## Entry Point

The entry point to the mapping specification is a `:Spec` object
      (here identified as the document itself), which gives basic information about the data source.
      Such an object can be declared by stating that the root URI of the type `:Spec` is.

    <#> a :Spec .

The spec resource expects the following metadata about the data source:

*   **Resource**
With resource, you declare the resources that the mapping process will output. The resources that are Each mapping for each resource that is defined here,
          <pre><#> :resource <#customer>, <#email>, <#phone>, <#affiliation>, <#sales_person> .</pre>
*   **Base URI**

    The base URI defines the namespace where the created resources will be based under.

The base URI can be fully declared:

    <#> :base_uri "<http://data.example.com/Test>" ;

or it can come of the package and/or resource name provided:
 For example, if the DataTank URI is `"https://data.example.com/"`, the package name is _Transport_ and the resource name is _Routes_, the base URI would be `"https://data.example.com/Transport/Routes"`.
 Therefore the `"tdt:package:resource"` definition for the aforementioned example would the same as `"https://data.example.com/Transport/Routes" ;`

    <#> :base_uri "tdt:package:resource" ;
    <#> :base_uri "https://data.example.com/Transport/Routes" ;

Turtle allows a shorter notation when several triples share the same subject. Declaring all above triples can be written like such:   
	<pre> 
	<#> a :Spec;
	:resource <#continent>, <#country>, <#region>, <#ourairports_region_page>, <#wikipedia_page>, <#dbpedia_resource>;
	:base_uri "http://data.example.com/dataset/example/".
	</pre>

# Declaring mappings

## Declaring and identifying resources

RDF uses unique URIs to identify resources. This means that these URIs need to be created from the dataset. This is done by declaring the resources and adding `:identify` property.

Declaring a resource works the same way as creating the `:Spec` object. This time, we choose a variable name, for example _Continent_, and say it is a `:Resource`.

    <#continent> a :Resource .

Next, we specify how the identifying URI should be created. The value of `:identify` is a _blank node_, which is a resource that does not really exists (and has no URI), and is added in Turtle between `[]`. Between these brackets, you can add predicates and objects as you would with any other resource. We use two predicates with our blank node:

### Reference to a single column

*   **Source column**

With source column, we define from which column, the values will be used to create the unique URI.
        This is typically a column with IDs (or primary keys), where the values are surely unique.
        For this, the predicate `:source_column` is used.

*   **CSV / database**

In the case of a CSV file, the reference to source column occurs either with an _integer_ value representing the column index (starting from 1),
        e.g. `":source_column" 2`,  or with the header value of the column, e.g. `:source_column "title"`.

*   **XML**

In the case of an XML file, the reference to the source node occurs with the sequence of nodes seperated by an underscore, e.g. `":source_column event_eventdetails_title"`.

In order to refer to an attribute of a value, again the the sequence of nodes seperated by an underscore should be used followed by `_attr_` and then the name of the attribute, e.g. `":source_column event_contactinfo_phone_attr_reservation"`</p>

If the same sequence of nodes leads to a level where there are more than one elements, the `event_eventdetails_mediafile_mediatype` refers to the first element. In order to refer to the subsequent, you need to define its index (_starting from 1_), e.g. `event_eventdetails_mediafile[2]_mediatype`.</p>
*   **JSON**

In the case of a JSON file, the reference to the source node occurs in the same way as with the XML files, with the sequence of nodes seperated by an underscore, e.g. `":source_column event_eventdetails_title"`.</p>

*   **Base URI**

Sometimes we want to use collections in our URIs. You can specify a different base URI using `:base_uri`, the value is a _string_.

For example, the following will create the uri _http://data.example.com/continents/Europe_ if a value in the fifth column would be "Europe".

<pre>
<#continent> a :Resource;
    :identity [
        :source_column 5;
        :base_uri "http://data.example.com/continents/"
    ] .
</pre>

<pre>
<#continent> a :Resource;
    :identity [
        :source_column "continent";
        :base_uri "http://data.example.com/continents/"
    ] .
</pre>

### Reference to multiple columns

In some case, resources will need to be combined in order to obtain a unique value (e.g., firstname and lastname).
      With the predicate `:source_columns` you can refer to the indexes of the resources you want to combine.

The value is two or more integers, or two or more column names, between brackets, separated by a space.
      By adding `:source_columns_glue`, you can define a string that will be used to glue the pieces together (e.g., underscore). A slash `/` is used to create a deeper hierarchy in the URI.
      The combined resources can be two or more columns of a CSV file, or two or more nodes of an XML or JSON file.
<pre>
<#ourairports_region_page> a :Resource;
  :type bibo:Webpage;
  :identity [
    :source_columns ( 6 3 ) ;
    :source_column_glue "/" ;
    :base_uri "http://www.ourairports.com/countries/"
  ].
</pre>

<pre>
<#Contact_Point> a :Resource;
  :type foaf:Agent;
  :identity [
    :source_columns ( "name" "surname" ) ;
    :source_column_glue "_" ;
    :base_uri "http://www.example.com/contacts/"
  ].
</pre>

### Using URI templates

Besides simple concatenation, the URI might be constructed in more complex manner.
      For that reason, URI templates are supported.
      URI templates provide a flexible way to specify a URI using variables, and asign values afterwards.
      The spec can be found [here](http://tools.ietf.org/html/rfc6570).

A URI template is added to value of `:identify`, by using the following predicates:

*   **Template**

You can specify a template using `:template`, the value is a _string_.

*   **Template variables**

The variables that need to fill the variables used in the template, are added using `:template_vars`.
        The value is a list of blank nodes, each specifying a variable name and a source column:

*   **Variable name**

This value represents the name of the variable used in the template. For this, the predicate `:variable` is used.
*   **Source column**

With source column, we define from which resource, the values will be used to create the unique URI.
        This is typically a column with IDs (or primary keys), where the values are surely unique.
        For this, the predicate `:source_column` is used, the value is an _integer_, representing the column index (starting from 1).

The above example can also be written as:

<pre>
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
</pre>

### Typing resources

Typing is the most basic operation in mapping. In the output RDF, it will create triples using the predicate `rdf:type` (or in Turtle the shorthand `a`). The object of this triple, will typically be a class, defined in the ontology that we are mapping to. The created resource, will therefore be an instance of this type. In this mapping language, we type a resource by using the `:type` predicate, followed by the URI of the concept.

    <#wikipedia_page> :type bibo:Webpage .

In this example, `bibo` represents the [Bibliographic Ontology Specification](http://purl.org/ontology/bibo/) namespace. Therefore, `bibo:Webpage` can also be written as `&lt;http://purl.org/ontology/bibo/Webpage>` .

## Declaring the predicates

Besides typing, you might want to add some related data to your resource. In RDF (or typically in [Linked Data](http://linkeddata.org/)), this is done in two ways. They both use a blank node as object.

### Adding relations to resources

Firstly, by adding _links_ to other resources, created from your data set or already existing on the Web. In this mapping language defined with the `:relationship` predicate. The object, a blank node, typically has two predicates:

*   The `:property` predicate, which refers to the property in the ontology you want to use.
*   The `:object_from` predicate, which refers to another resource, declared elsewhere in the document.
    
	<pre>
	<#region> :relationship [
	  :property owl:sameAs;
	  :object_from <#dbpediaRsource>
	] .
	</pre>
	<pre>
	<http://test.com/REGION11> owl:sameAs &#60;http://dbpedia.org/resource/Aix-en-Provence> .
	</pre>

An example with relationships:

    <#region> :relationship [ :property ex:prop; :object <http://example.com/resource>]

You can also use the current subject as object, in order to create inverse relations:

<pre>
:relationship [ :property ex:parent; :subject <http://example.com/resource> ] 
</pre>

### Adding attributes to resources

Secondly, by adding triples with _literals_ as object value. In many cases, the values of a resource (_column or node_) just need to be added as a string or integer. This is achieved with the `:attribute` predicate.  The object, a blank node, typically has these predicates:

*   The `:property` predicate, which refers to the property in the ontology you want to use to add the literal value.
*   The `:source_column` predicate, which refers to a resource.*   The `:language` predicate, which can add a language code to a string value.
*   The `:datatype` predicate, which adds a XSD datatype to the value

    <pre>
    <#region> [
      :property geo:alt;
      :source_column "altitude";
      :datatype xsd:float;
    ] .
    </pre>
    <pre><http://test.com/REGION11> geo:alt "3.14"^^xsd:float .</pre>
    <pre>
    <#region> [
      :property rdfs:label;
      :source_column "region";
      :language "en";
    ] .
    </pre>

### Constants

Attributes and relationships can also be used to add constant literals or URIs that are hard coded in the mapping file (e.g., adding unit information for values).
    The predicates `:value` (attributes) and `:object` (relationships) are used for this.

An example with attributes:

    <#region> :attribute [ :property ex:prop; :value "something"]

### A fully mapped resource

A fully mapped resource typically consists of typing, identity, relations and attributes, as shown in the combined example below:

<pre>
<#region> a :Resource;
  :type places:Region;
  :identity [
    :source_column 2;
	:base_uri "http://data.example.com/dataset/world-geography/regions/"
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
</pre>

## Processing source values

Source values (_column and node values_) can be processed before using them.
    This is done by calling a process function, which is specified by adding the predicate `:process`.
    Its object is an ordered list of defined functions which will be executed sequentially.
    We discuss some of them in detail.

### Using conversions

Conversions transform a value into another value and are specified as custom PHP functions.
    These function are specified in the class `Conversions` located at `custom/conversions.class.php`. 
<pre>
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
</pre>

This class can be extended with extra functions.
    In the mapping file, a function is called by adding `:functionname` to the object list.
    In the following example, we add the altitude of a region.
    The values in the data sources (_columns_) are in feet, so we use a process function to convert them.

<pre>
<#region> a :Resource;

:attribute [
  :property geo:alt;
  :source_column 7;
  :datatype xsd:float;
  :process (:feet_to_metres)
] .
</pre>

### Using regular expressions

With the Vertere mapping language, you can also specify regular expression patterns to transform the values in certain sources (_columns or nodes_).

In this example, we transform the Wikipedia URIs, defined in another resource `<#wikipedia_page>`, to DBpedia URIs. We can use this `&lt;#dbpedia_resource>` elsewhere as an object. (e.g., to create _sameAs_ links)

<pre>
<#dbpedia_resource> a :Resource; 
  :identity [
    :source_resource <#wikipedia_page>;
    :base_uri "" ;
    :process ( :regex );
    :regex_match "http://[^/]*/wiki/(.*)";
    :regex_output "http://dbpedia.org/resource/${1}";
  ] .
</pre>

### Using lookup to replace a certain value

With the Vertere mapping language, you can specify a value that should be mapped every time a key appears in a certain resource (_column or node_).

<pre>
<#boolean_lookup> a :Lookup;
  :lookup_entry [ 
    :lookup_key "True", "1"; 
    :lookup_value "true"
  ] ;
  :lookup_entry [ 
    :lookup_key "False", "0"; 
    :lookup_value "false"
  ] .
</pre>

### Using lookup to compaire a key with the value of a column

With the Vertere mapping language, you can compare the value of a certain resource (_column or node_) with a key value.
     If the lookup key `:lookup_key` is similar to the source column value `:source_column`, the value of the lookup column `:lookup_column` will be returned.

This type of lookup can be used in combination with the `:identity` definition to be decided wether the URI will be created or not depending on the value of a certain resource (_column or value_).

<pre>
<#Media> a :Resource
; :type schema:ImageObject
; :identity [
    :source_column "mediatype" ;
    :lookup <#mediatype_lookup> ;
    :base_uri ""
    ]
; :attribute
    [ :property wt:mediatype; :source_column "mediatype" ],
    [ :property exif:copyright; :source_column "copyright" ]
.

 <#mediatype_lookup> a :Lookup;
    :lookup_entry [ 
    :lookup_key "imageweb"; 
    :lookup_column "hlink"
  ] .
</pre>   

## Transforming values with process functions

*   **Normalize function**

    `:normalise` is used to normalize (canonicalize) a string.
    <pre>
    <#Event> a :Resource
      ; :type foaf:Agent
      ; :identity [
        :source_column "title" ;
        :process ( :normalise );
        :base_uri "tdt:package:resource/contact_point/"
      ] .</pre>
      
*   **Trim quotes**
`:trim_quotes`

*   **Flatten utf8**
`:flatten_utf8`
        
*   **Title case**
`:title_case`

*   **URL encode**

`:url_encode` can be used to encoding a string to be used in a art of a URL.

### Combining techniques

Many of the above techniques can be combined.

# Full example

The following example maps a CSV file with airports using the TRANSIT, PLACES, GEO, GEORSS, NAPTAN, FOAF, FLY, SPACEREL and BIBO volcabulary.

<pre>
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
</pre>
<pre>
@prefix : &lt;http://example.com/schema/data_conversion#> .
@prefix bibo: &lt;http://purl.org/ontology/bibo/> .
@prefix fly: &lt;http://vocab.org/fly/schema/> .
@prefix foaf: &lt;http://xmlns.com/foaf/0.1/> .
@prefix geo: &lt;http://www.w3.org/2003/01/geo/wgs84_pos#> .
@prefix georss: &lt;http://www.georss.org/georss/> .
@prefix naptan: &lt;http://transport.data.gov.uk/def/naptan/> .
@prefix owl: &lt;http://www.w3.org/2002/07/owl#> .
@prefix places: &lt;http://purl.org/ontology/places#> .
@prefix rdf: &lt;http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: &lt;http://www.w3.org/2000/01/rdf-schema#> .
@prefix spacerel: &lt;http://data.ordnancesurvey.co.uk/ontology/spatialrelations/> .
@prefix transit: &lt;http://vocab.org/transit/terms/> .
@prefix xsd: &lt;http://www.w3.org/2001/XMLSchema#> .

<#> a :Spec;
:resource &lt;#airport>, &lt;#country>, &lt;#continent>, &lt;#region>, &lt;#boolean&#095;lookup>, &lt;#municipality>, &lt;#ourairports&#095;page>, &lt;#naptan&#095;resource>, &lt;#airport&#095;type>;
:base_uri "http://data.example.com/airports/" .

<#airport> a :Resource
; :identity [ :source_column "ident"; ]
; :type fly:Airport, transit:Stop, naptan:Airport
; :relationship
	[ :property rdf:type; :object_from <#airport_type> ],
	[ :property spacerel:within; :object_from <#municipality> ],
	[ :property spacerel:within; :object_from <#region> ],
	[ :property spacerel:within; :object_from <#country> ],
	[ :property spacerel:within; :object_from <#continent> ],
	[ :property foaf:isPrimaryTopicOf; :object_from <#ourairports_page> ],
	[ :property owl:sameAs; :object_from <#naptan_resource> ]

; :attribute
	[ :property geo:lat; :source_column "latitude_deg"; :datatype xsd:float ],
	[ :property geo:long; :source_column "longitude_deg"; :datatype xsd:float ],
	[ :property geo:alt; :source_column "elevation_ft"; :datatype xsd:float; :process ( :feet_to_metres ); ],
	[ :property georss:point; :source_columns ("latitude_deg" "longitude_deg"); :source_column_glue " " ],
	[ :property foaf:name; :source_column "name"; :language "en" ],
	[ :property fly:icao_code; :source_column "ident" ],
	[ :property fly:scheduled_service; :source_column "scheduled_service"; :lookup <#boolean_lookup> ]
.

<#continent> a :Resource
; :identity [
	:source_column "continent";
	:base_uri "http://data.example.com/world-geography/continents/"
].

<#country> a :Resource;
    :identity [
        :source_column "iso_country";
        :base_uri "http://data.example.com/world-geography/countries/"
    ].

<#region> a :Resource;
    :type places:Region;
    :identity [
        :source_column "iso_region";
        :base_uri "http://data.example.com/world-geography/regions/"
	];
    :relationship [
        :property spacerel:contains;
        :object_from <#municipality>
    ] .

<#airport_type> a :Resource
; :identity [
	:source_column "type";
	:process ( :normalise :title_case )
; :base_uri "http://data.example.com/airports/schema/" ]
; :type rdfs:Class

; :attribute[
	:property rdfs:label;
	:source_column "type";
	#:process ( :regex :title_case );
	:regex_match "_";
	:regex_output " ";
].

<#municipality> a :Resource;
    :type places:Municipality;
    :identity [
        :source_column "municipality";
        :container "municipalities";
        #:process ( :flatten_utf8 :normalise );
	];
    :relationship [
        :property spacerel:within;
        :object_from <#region>
    ];
    :attribute [
        :property rdfs:label;
        :source_column "municipality"
    ] .

<#ourairports_page> a :Resource;
    :type bibo:Webpage;
    :identity [
        :source_column "ident";
        :base_uri "";
        :process ( :regex );
        :regex_match "^(.*)$";
        :regex_output "http://www.ourairports.com/airports/${1}/";
	] .

<#naptan_resource> a :Resource; 
  :identity [
	:source_column "iata_code";
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

</pre>
