const $ = require('jquery');
import instantsearch from 'instantsearch.js'
import algoliasearch from 'algoliasearch/lite';
import {searchBox, hits, pagination, hierarchicalMenu, stats} from "instantsearch.js/es/widgets";

function algoliaAutocomplete(algoliaConfig) {
    var client = algoliasearch(algoliaConfig.applicationId, algoliaConfig.searchKey)
    var projects = client.initIndex(algoliaConfig.searchPrefix + 'projects');
    var classifications = client.initIndex(algoliaConfig.searchPrefix + 'classifications');
    var organization = client.initIndex(algoliaConfig.searchPrefix + 'organization');
    var user = client.initIndex('user');

    autocomplete('#search-form-input', {} ,[
        {
            source: autocomplete.sources.hits(projects, { hitsPerPage: 5 }),
            displayKey: 'name',
            templates: {
                header: '<div class="aa-suggestions-category">Projects</div>',
                suggestion: function(suggestion) {
                    return '<span>' +
                        suggestion._highlightResult.name.value + '</span><span>'
                        + suggestion._highlightResult.displayName.value + '</span>';
                }
            }
        },
        {
            source: autocomplete.sources.hits(organization, { hitsPerPage: 5 }),
            displayKey: 'name',
            templates: {
                header: '<div class="aa-suggestions-category">Organizations</div>',
                suggestion: function(suggestion) {
                    return '<span>' +
                        suggestion._highlightResult.name.value + '</span>  <span>'
                        + suggestion._highlightResult.displayName.value + '</span>';
                }
            }
        },
        {
            source: autocomplete.sources.hits(user, { hitsPerPage: 5 }),
            displayKey: 'name',
            templates: {
                header: '<div class="aa-suggestions-category">Users</div>',
                suggestion: function(suggestion) {
                    var name = suggestion._highlightResult.name ?
                        suggestion._highlightResult.username.value : suggestion._highlightResult.name.value;
                    return '<span>' +
                        name + '</span>  <span>'
                        + suggestion._highlightResult.username.value + '</span>';
                }
            }
        }
    ]).on('autocomplete:selected', function(event, suggestion, dataset) {
        if(dataset === 1) {
            $('#search-form-input').val(suggestion['name'])
            $('#type').val('projects');
        }
        if(dataset === 2) {
            $('#search-form-input').val(suggestion['name'])
            $('#type').val('organization');
        }
        if(dataset === 3) {
            $('#search-form-input').val(suggestion['username'])
            $('#type').val('user');
        }
    });
}

function searchFunctions() {
    $('.search-type').on('click',function(event){
        event.preventDefault();
        $('#type').val($(this).attr('id'));
        $('#q').val($(".ais-SearchBox-input").val());
        $('.search-form').submit();
    });
    $('.search-query').on('click',function(event){
        event.preventDefault();
        $('#q').val($(".ais-SearchBox-input").val());
        $('.search-form').submit();
    });
}
// Algolia instantsearch configuration
function algoliaInstantSearch(options, searchType) {
    var search = instantsearch({
        searchClient: algoliasearch(
            options.appId,
            options.apiKey,
        ),
        indexName: options.searchPrefix + options.indexName,
        routing: true,
        searchParameters: {
            hitsPerPage: 10,
        },
        searchFunction: options.searchFunction,
    });

    // Search Box Configuration
    search.addWidget(
        searchBox({
            container: "#search-input",
            placeholder: 'Search ...',
        })
    );

    // Search Result Configuration
    search.addWidget(
        hits({
            container: "#hits",
            templates: {
                item: getTemplate(searchType),
                empty: '<h2>Nothing found :-( Maybe try another search keyword?</h2>',
            },
            transformData: {
                item : function item(item) {
                    if(searchType === 'projects') {
                        item.activityDetails = getTimeDiff(item);
                        item.creationDetails = getFormattedDate(item.dateAdded.date);
                    }
                    if(searchType === 'user') {
                        item.createdAt.date = getFormattedDate(item.createdAt.date)
                    }
                    return item;
                },
            },
        })
    );

    // Pagination
    search.addWidget(
        pagination({
            container: '#pagination',
            scrollTo: '#search-input',
        })
    );

    search.addWidget(
        stats({
            container: "#stats",
            transform: {

            },
        })
    );

    if(searchType === 'projects') {
        search.addWidget(
            hierarchicalMenu({
                container: "#hierarchical-categories",
                attributes: [
                    'hierarchicalCategories.lvl0',
                    'hierarchicalCategories.lvl1',
                    'hierarchicalCategories.lvl2',
                    'hierarchicalCategories.lvl3'
                ],
                separator: '::',
                templates: {
                    header: '<h3>Classifications</h3>',
                    item:  '<a href="{{url}}" class="facet-item {{#isRefined}}active{{/isRefined}}"><span class="facet-name"> {{label}}</span class="facet-name"><span class="ais-hierarchical-menu--count">{{count}}</span></a>'
                },
            })
        );
    }

    search.start();
}

// Get Templates for Result display feature
function getTemplate(templateName) {
    return $('#'+templateName+'-template').html();
}

function getTimeDiff(item) {
    if(item.dateLastActivityOccurred === null) {
        return 0 + ' days ago';
    }
    var time = item.dateLastActivityOccurred.date;
    var date = new Date(time);
    var today = new Date();
    var years = today.getFullYear() - date.getFullYear();
    var months = today.getMonth() - date.getMonth();
    var days = today.getUTCDate() - date.getUTCDate();

    if(years > 0) {
        return years === 1 ? years + ' year ago' : years + ' years ago';
    }

    if(months > 0) {
        return months === 1 ? months + ' month ago' : months + ' months ago';
    }

    if(days > 0) {
        return days === 1 ? days + ' day ago' : days + ' day ago';
    }

    return 0 + ' day ago'
}

function getFormattedDate(item){
    var timeToFormat = item;
    var date =  new Date(timeToFormat);
    var formattedDate = date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate();
    return formattedDate;
}


// Search-page specific javascript
$(function() {
    searchFunctions();
    let options =  {
        appId: $("#search-data").data("search").appId,
        apiKey: $("#search-data").data("search").apiKey,
        indexName: $("#search-data").data("search").indexName,
        searchPrefix: $("#search-data").data("search").searchPrefix,
        searchParameters: {
            hitsPerPage: 10,
        },
    };
    // Algolia instantsearch configuration
    algoliaInstantSearch(options, options.indexName);
});
