'use strict';

var app = angular.module('rockwellApp', ['ngSanitize']);

app.controller('DataController', ['$scope', '$http', '$sce', function ($scope, $http, $sce) {

	$scope.statusData = [];
	$scope.count      = 10;
	$scope.sortorder  = 'date';

	// Initialize data
	getData();

	$scope.sortBy = function(param) {
		$scope.sortorder = param;
	}

	// Fetch data for passed handle
	$scope.fetchDataForHandle = function() {
		$scope.loading = true;

		// Call twitterAccess php file
		$http.post('twitterAccess.php', {
			handle: $scope.handle,
			count : $scope.count
		}, {
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).success(function (response) {

			console.log(response);

			if (response.success)
				getData();
			else
				$scope.loading = false;
		});
	};

	// Gets all data from the data store
	function getData() {
		$scope.loading = true;

		// Load existing data into display table
		$http.get('dataStore.php').success(function(response) {

			if (response.success) {
				var dataToStore = [];
				for (var i = 0; i < response.data.length; i++) {
					dataToStore.push({
						handle   : response.data[i].handle,
						name	 : response.data[i].name,
						text	 : response.data[i].text,
						createdAt: new Date(response.data[i].createdAt)
					});
				}
				$scope.statusData = dataToStore;
			}

			$scope.loading = false;
		});
	};

}]);