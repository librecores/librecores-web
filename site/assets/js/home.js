import $ from 'jquery';

import algoliasearch from 'algoliasearch/lite';
import autocomplete from 'autocomplete.js'

import * as FRONTEND_CONFIG from './frontend-config.json';

function newHitsSource(index, params) {
  return function doSearch(query, cb) {
    index
      .search(query, params)
      .then(function(res) {
        cb(res.hits, res);
      })
      .catch(function(err) {
        console.error(err);
        cb([]);
      });
  };
}

function algoliaAutocomplete(algoliaConfig) {
  var client = algoliasearch(algoliaConfig.applicationId, algoliaConfig.searchKey)
  var projects = client.initIndex(algoliaConfig.searchPrefix + 'projects');
  var classifications = client.initIndex(algoliaConfig.searchPrefix + 'classifications');
  var organization = client.initIndex(algoliaConfig.searchPrefix + 'organization');
  var user = client.initIndex('user');



  autocomplete('#search-form-input', {} ,[
      {
          source: newHitsSource(projects, { hitsPerPage: 5 }),
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
          source: newHitsSource(organization, { hitsPerPage: 5 }),
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
          source: newHitsSource(user, { hitsPerPage: 5 }),
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

$(function() {
  // do not use autofocus attribute to work around FF bug 712130 (FOUC)
  $("#search-form-input").focus();
  let options =  {
      applicationId: FRONTEND_CONFIG.algolia.appId,
      searchKey: FRONTEND_CONFIG.algolia.searchKey,
      searchPrefix: FRONTEND_CONFIG.algolia.searchPrefix,
  };
  algoliaAutocomplete(options);
});
