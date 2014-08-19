'use strict';

/* Controllers */
var filmSelectorApp = angular.module('filmSelectorApp', []);

filmSelectorApp.controller('FilmPairCtrl',['$scope', '$http', function ($scope, $http) {
    //var favoriteCookie = $cookies.settings;
    $scope.filmOne = null;
    $scope.filmTwo = null;

    $scope.selectedOne = null;
    $scope.selectedTwo = null;

    $scope.radioModel = 'SportData';

    $scope.compare = function() {
//        $http.get('data/lm/'+$scope.selectedOne.id).success(function(data) {
//        $http.get('data/lm/2842437').success(function(data) {

            var w = d3.select("section[id=graph]")[0][0].offsetWidth,
                h = d3.select("section[id=graph]")[0][0].offsetHeight,
                radius = d3.scale.linear().domain([0, 978000]).range(["2", "30"]),
                loading,
                root,
                json,
                link,
                linkedByIndex = {},
                node,
                labels = [],
                selectedLabelIndex = null;
            d3.select("section[id=graph]").selectAll("*").remove();
            var vis = d3.select("section[id=graph]").append("svg:svg")
                .attr("width", w)
                .attr("height", h);

            function fade(opacity, showText) {
                return function(d, i) {
                    labels = [];
                    var selectedLabelData = null;
                    node.style("fill-opacity", function(o) {
                        var isNodeConnectedBool = isNodeConnected(d, o);
                        var thisOpacity = isNodeConnectedBool ? 1 : opacity;
                        if (!isNodeConnectedBool) {
                            this.setAttribute('style', "stroke-opacity:"+opacity+";fill-opacity:"+opacity+";");
                        } else {
                            labels.push(o);
                            if (o == d) selectedLabelData = o;
                        }
                        return thisOpacity;
                    });

                    link.style("stroke-opacity", function(o) {
                        return o.source === d || o.target === d ? 1 : opacity;
                    });

                    labels.sort(function(a, b){return b.value - a.value})

                    selectedLabelIndex = labels.indexOf(selectedLabelData);

                    vis.selectAll("text.nodetext").data(labels).enter().append("svg:text")
                        .attr("class", function(d){ return "nodetext type"+d.type})
                        .text(function(d){ return d.name + (d.type != 3 ? ':' + numberWithCommas(d.value) : '')})
                        .style("font-weight", function(o){ return d.index == o.index ? 'bold' : 'normal'})
                        .attr("x", 0)
                        .attr("y", function(d, i){ return this.getBBox().height * (i+1)});
                }
            }

            function normalizeNodesAndRemoveLabels() {
                return function(d, i) {
                    selectedLabelIndex = null;
                    vis.selectAll(".link").style("stroke-opacity", 1);
                    vis.selectAll(".circle").style("stroke-opacity", 1).style("fill-opacity", .5).style("stroke-width", 1);
                    vis.selectAll(".nodetext").remove();
                }
            }

            function isNodeConnected(a, b) {
                return linkedByIndex[a.index + "," + b.index] || linkedByIndex[b.index + "," + a.index] || a.index == b.index;
            }

            function openLink() {
                return function(d) {
                    window.open("http://www.imdb.com/find?q="+d.slug+"&s=all")
                }
            }

            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(?:\d{3})+(?!\d))/g, ",");
            }

            var initialized = false;
            function tick(e) {
                //do not render initialization frames because they are slow and distracting
                if (e.alpha < 0.01 ) {
                    vis.select(".loading").remove();

                    node
                        .transition()
                        .duration(1000)
                        .attr("cx", function(d) { return d.x; })
                        .attr("cy", function(d) { return d.y; })

                    link
                        .transition()
                        .duration(1000)
                        .attr("x1", function(d) { return d.source.x; })
                        .attr("y1", function(d) { return d.source.y; })
                        .attr("x2", function(d) { return d.target.x; })
                        .attr("y2", function(d) { return d.target.y; })

                    force.stop()
                } else {
                    if (e.alpha < 0.015 ) {
                        var q = d3.geom.quadtree(root.nodes),
                            i = 0,
                            n = root.nodes.length;
                        while (++i < n) {
                            q.visit(collide(root.nodes[i]));
                        }
                    }
//                    loadingText.text(function(){return "Calculating Optimum Layout: " + Math.round((1 - (e.alpha * 10 - 0.1)) * 100) + "%"});
                }
            }

            var force = self.force = d3.layout.force()
                .linkDistance(0)
                .charge(-350)
                .gravity(2)
                .size([w, h])
                .on("tick", tick);

            d3.json('data/lm/'+$scope.selectedOne.id, function(json) {
                root = json;

                update();

//                loadingText = vis.append("svg:text")
//                    .attr("class", "loading")
//                    .attr("x", (w/2)-200)
//                    .attr("y", h/2)
//                    .text("Loading");
            });

            function update() {
                linkedByIndex = {}
                root.links.forEach(function(d) {
                    linkedByIndex[d.source + "," + d.target] = 1;
                });

                force.nodes(root.nodes.map(function(d) {
                        return jQuery.extend(d, {
                            radius: d.type == 3 ? 5 : radius(d.value),
                            x: Math.random() * w,
                            y: Math.random() * h
                        })
                    }))
                    .links(root.links)
                    .start();

                link = vis.selectAll(".link")
                    .data(root.links)

                link.enter().append("svg:line")
                    .attr("class", function(d){ return "link type"+d.target.type})
                    .attr("x1", function(d) { return d.source.x; })
                    .attr("y1", function(d) { return d.source.y; })
                    .attr("x2", function(d) { return d.target.x; })
                    .attr("y2", function(d) { return d.target.y; })

                link.exit().remove();

                node = vis.selectAll(".circle")
                    .data(root.nodes);

                node.enter()
                    .append("svg:circle")
                    .attr("class", function(d){ return "circle type"+d.type})
                    .attr("r", function(d) { return d.radius })
                    .on("mouseover", fade(.1, true))
                    .on("mouseout", normalizeNodesAndRemoveLabels())
                    .attr("cx", function(d) { return d.x })
                    .attr("cy", function(d) { return d.y })
                    .on("click", openLink())

                node.exit().remove();
            }

            function collide(node) {
                var r = node.radius + 50,
                    nx1 = node.x - r,
                    nx2 = node.x + r,
                    ny1 = node.y - r,
                    ny2 = node.y + r;
                return function(quad, x1, y1, x2, y2) {
                    if (quad.point && (quad.point !== node)) {
                        var x = node.x - quad.point.x,
                            y = node.y - quad.point.y,
                            l = Math.sqrt(x * x + y * y),
                            r = node.radius + quad.point.radius;
                        if (l < r) {
                            l = (l - r) / l * .5;
                            node.x -= x *= l;
                            node.y -= y *= l;
                            quad.point.x += x;
                            quad.point.y += y;
                        }
                    }
                    return x1 > nx2
                        || x2 < nx1
                        || y1 > ny2
                        || y2 < ny1;
                };
            }

            $(document).keydown(function(e){
                if (e.keyCode == 13 && selectedLabelIndex !== null) {
                    openLink()(labels[selectedLabelIndex]);
                    return false;
                } else if (e.keyCode == 38 || e.keyCode == 40 && selectedLabelIndex !== null) {
                    if(e.keyCode == 38) selectedLabelIndex--;
                    if(e.keyCode == 40) selectedLabelIndex++;
                    if(selectedLabelIndex < 0) selectedLabelIndex = labels.length -1;
                    if(selectedLabelIndex > labels.length - 1) selectedLabelIndex = 0;

                    vis.selectAll("text.nodetext").style("font-weight", function(d, i){
                        return labels[selectedLabelIndex] == d ? "bold" : "normal"
                    });

                    vis.selectAll("circle.circle").style("stroke-width", function(d, i){
                        return labels[selectedLabelIndex] == d ? "5" : "1"
                    });

                    return false;
                } else if (e.keyCode == 39) {
                    window['force']['charge'](window['force']['charge']() - 10)
                    force.start();
                } else if (e.keyCode == 37) {
                    window['force']['charge'](window['force']['charge']() + 10)
                    force.start();
                }
            });


    };

    $scope.filmSelect = function($film, $selector){
        $http.get('data/sm/'+encodeURI($film)).success(function(data) {
            if ($selector == 'one'){
                $scope.filmOne = data;
                return 0;
            }
            $scope.filmTwo = data;

        });
    };




}]);