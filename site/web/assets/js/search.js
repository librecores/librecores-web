function algoliaAutocomplete() {
  var client = algoliasearch("JWXAXWPD3Z", "d7e6874d3bae89103fc6133ae81e4aeb")
  var projects = client.initIndex('projects');
  var classifications = client.initIndex('classifications');
  var organization = client.initIndex('organization');
  var user = client.initIndex('user');

  autocomplete('#search-form-input', {debug:true}, [
    {
      source: autocomplete.sources.hits(classifications, { hitsPerPage: 3 }),
      displayKey: 'classifications',
      templates: {
        header: '<div class="aa-suggestions-category">Classifications</div>',
        suggestion: function(suggestion) {
          return '<span>' +
            suggestion._highlightResult.classifications.value + '</span>  <span>'
            + suggestion._highlightResult.projectName.value + '</span>';
        }
      }
    },
    {
      source: autocomplete.sources.hits(projects, { hitsPerPage: 3 }),
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
      source: autocomplete.sources.hits(organization, { hitsPerPage: 3 }),
      displayKey: 'name',
      templates: {
        header: '<div class="aa-suggestions-category">Organizations</div>',
        suggestion: function(suggestion) {
          console.log(suggestion)
          return '<span>' +
            suggestion._highlightResult.name.value + '</span>  <span>'
            + suggestion._highlightResult.displayName.value + '</span>';
        }
      }
    },
    {
      source: autocomplete.sources.hits(user, { hitsPerPage: 3 }),
      displayKey: 'name',
      templates: {
        header: '<div class="aa-suggestions-category">Users</div>',
        suggestion: function(suggestion) {
          return '<span>' +
            suggestion._highlightResult.name.value + '</span>  <span>'
            + suggestion._highlightResult.username.value + '</span>';
        }
      }
    }
  ]).on('autocomplete:selected', function(event, suggestion, dataset) {
    console.log()
    if(dataset === 1) {
      $('#search-form-input').val(suggestion['classifications'])
    }
    if(dataset === 2) {
      $('#search-form-input').val(suggestion['name'])
    }
    if(dataset === 3) {
      $('#search-form-input').val(suggestion['name'])
    }
    if(dataset === 4) {
      $('#search-form-input').val(suggestion['username'])
    }
  });
}
