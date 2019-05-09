import $ from 'jquery';

import instantsearch from 'instantsearch.js'
import 'instantsearch.js/dist/instantsearch.css'
import {searchBox, hits, pagination, hierarchicalMenu, stats} from "instantsearch.js/es/widgets";

import algoliasearch from 'algoliasearch/lite';

import * as FRONTEND_CONFIG from './frontend-config.json';

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
        return days === 1 ? days + ' day ago' : days + ' days ago';
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
      appId: FRONTEND_CONFIG.algolia.appId,
      apiKey: FRONTEND_CONFIG.algolia.searchKey,
      searchPrefix: FRONTEND_CONFIG.algolia.searchPrefix,
      // indexName is subject to change on form-input
      indexName: $("#search-data").data("search").indexName,
      searchParameters: {
          hitsPerPage: 10,
      },
  };
  // Algolia instantsearch configuration
  algoliaInstantSearch(options, options.indexName);
});
