# LibreCores Sample Page

You can use MarkDown syntax, HTML and TWIG templating freely intermixed.
The processing pipeline is Markdown -> HTML -> TWIG -> HTML

<marquee>this is the most ugly and outdated embedded HTML</marquee>

Example for a twig expression:
now is {{ "now"|date("d.m.Y") }}

## Some link examples

 - [external link](http://google.com)
 
 - [link to a static page]({{ path('librecores_site_page', {'page': 'documentation/docs'}) }})
 
 - [link to another bundle]({{ path('librecores_core_repo_homepage') }})
 
 - [link to an IP core in the repo]({{ path('librecores_core_repo_viewcore', {'vendor': 'philipp', 'name': 'test'}) }})

