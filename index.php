<!DOCTYPE html>
<html data-ng-app="rockwellApp">
	<head>
		<title>Rockwell PHP Development Lead Test</title>
        <link rel="stylesheet" type="text/css" href="css/bootstrap-3.1.1.css"/>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
	</head>
	<body data-ng-controller="DataController">
        
        <br>

        <!-- Header -->
        <?php include 'partials/header.partial.php'; ?>

        <!-- Controls -->
		<section class="container">
            <div class="col-md-7">
                <h2>Instructions:</h2>
                <br>
                <ol class="lead">
                    <li>Type any Twitter handle into the search box.</li>
                    <li>Provide the maximum number of tweets to receive.</li>
                    <li>Click the Search button.</li>
                </ol>
                <p>
                    Note: The database will populate as searches are performed, but no single tweet will be entered more than once into the database. Also, you can click on the table headers to sort by that column.
                </p>
            </div>
            <div class="col-md-5">
                <form name="fetchStatusForm">
                    <h2>Control Panel:</h2>
                    <br>
                    <div class="row">
                        <div class="col-md-8">
                            <label>Twitter handle <small>(without @)</small>:</label>
                            <input type="text" 
                                class="form-control" 
                                name="handle" 
                                placeholder="Enter handle" 
                                required 
                                data-ng-model="handle" />
                        </div>
                        <div class="col-md-4">
                            <label>No of tweets:</label>
                            <input type="number" class="form-control" name="count" value="10" data-ng-model="count" />
                        </div>
                    </div>
                    <br>
                    <button class="btn btn-primary pull-right" data-ng-click="fetchDataForHandle()">Search!</button>
                </form>
            </div>
        </section>

        <hr>

        <section class="container">
            <br>
            <div class="lead text-center" data-ng-show="loading">
                <img src="img/ajax-loader.gif" alt="Loading, please wait..." /> Loading data, please wait...
            </div>
            
            <!-- Table to display data -->
            <div class="row" data-ng-hide="loading">
                <div class="col-md-12">
                    <table class="table table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <th data-ng-click="sortBy('handle')" data-ng-class="{selected:sortorder==='handle'}">Handle</th>
                                <th data-ng-click="sortBy('name')" data-ng-class="{selected:sortorder==='name'}">Name</th>
                                <th data-ng-click="sortBy('text')" data-ng-class="{selected:sortorder==='text'}">Status</th>
                                <th data-ng-click="sortBy('date')" data-ng-class="{selected:sortorder==='date'}">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr data-ng-repeat="status in statusData | orderBy:sortorder">
                                <td>@{{ status.handle }}</td>
                                <td>{{ status.name }}</td>
                                <td>{{ status.text }}</td>
                                <td>{{ status.createdAt | date:'short' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <?php include 'partials/footer.partial.php'; ?>


        <!-- Scripts -->
        <script src="js/vendor/angular.min.js"></script>
        <script src="js/vendor/angular-sanitize.min.js"></script>
        <script src="js/app.js"></script>

	</body>
</html>