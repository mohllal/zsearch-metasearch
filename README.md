# Z-Search
Z-Search is a PHP metasearch engine application which uses Google, Bing, and Ask search engines as the underlying datasource.

Z-Search takes users query and returns a list of documents intended to satisfy their information needs. It is of the metasearch class, which means that it uses other underlying search engines and creates a list of the best results from all of them. The idea is that the results returned would be better than any single search engine on its own.

[From Wikipedia](https://en.wikipedia.org/wiki/Metasearch_engine): A metasearch engine (or aggregator) is a search tool that uses another search engine's data to produce their own results from the Internet. Metasearch engines take input from a user and simultaneously send out queries to third party search engines for results. Sufficient data is gathered, formatted by their ranks and presented to the users.

### General Features:
- Data are retrieved from three different search engines.
- [Query Tokenizing](https://nlp.stanford.edu/IR-book/html/htmledition/tokenization-1.html).
- [Query Stemming](https://en.wikipedia.org/wiki/Stemming).
- [Query Expansion](https://en.wikipedia.org/wiki/Query_expansion).
- [Document Clustering](https://en.wikipedia.org/wiki/Document_clustering).
- Responsive design.
- Database support.

### Search Features:
- Aggregated: Results merged and displayed into a single, and optimised list.
- Non-Aggregated: Results are returned and displayed from each search engine individually.
- Clustered: Results merged into one list with categories which depends on the selected clustering type.

### Stemming Algorithms:
- [Porter Algorithm](https://tartarus.org/martin/PorterStemmer/): The Porter stemming algorithm is a process for removing the commoner morphological and inflexional endings from words in English. Its main use is as part of a term normalisation process that is usually done when setting up Information Retrieval systems.
- [Porter2](http://snowball.tartarus.org/algorithms/english/stemmer.html): An improvement of Porter stemmer made by [Snowball](http://snowballstem.org).

### Clustering Types:
- Term frequency: It is intended to reflect how important a word is to a document in a collection or corpus. It calculates number of times a word appears in the document, but is often offset by the frequency of the word in the corpus, which helps to adjust for the fact that some words appear more frequently in general. 
- Binaclustering: A fixed number of unique categories are created and documents will only appear in one cluster.

### Usage:
1. Clone or download this repository to your machine.
2. Move project files to your local web server environment e.g. [WampServer](http://www.wampserver.com/en/).
This [article](https://www.sitepoint.com/using-wampserver-for-local-development/) explains how to use WampServer for local development.
3. Import ```database_setup.sql``` file in your MySQL administration tool e.g. [phpMyAdmin](https://www.phpmyadmin.net/).
This [article](https://my.bluehost.com/cgi/help/256) explains how to import MySQL database using phpMyAdmin


### Built with:
- [PHP](http://php.net/): PHP is a widely-used open source general-purpose scripting language that is especially suited for web development and can be embedded into HTML.
- [MySQL](https://www.mysql.com/): MySQL is an open source relational database management system (RDBMS) based on Structured Query Language (SQL).
- [Bootstrap 2](http://getbootstrap.com/2.3.2/): Bootstrap is the most popular HTML, CSS, and JS framework for developing responsive, mobile first projects on the web.
- [JQuery](https://jquery.com/): jQuery is a fast, small, and feature-rich JavaScript library.
- [Google CSE API](https://developers.google.com/custom-search/json-api/v1/overview): Custom Search API helps users develop websites to retrieve and display search results from Google Custom Search programmatically. With this API, users can use RESTful requests to get either web search or image search results in JSON or Atom format.
- [Bing Web Search API](https://azure.microsoft.com/en-us/services/cognitive-services/bing-web-search-api/): The Web Search API provides a similar experience to Bing search by returning search results that Bing determines are relevant to the specified user's query. The results include webpages and may include images, videos, and more. 

### License:
This software is licensed under the [Modified BSD License](https://opensource.org/licenses/BSD-3-Clause).

