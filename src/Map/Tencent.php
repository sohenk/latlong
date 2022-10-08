<?php

namespace Encore\Admin\Latlong\Map;

class Tencent extends AbstractMap
{
    /**
     * @var string
     */
    protected $api = '//map.qq.com/api/js?v=2.exp&key=%s&libraries=place';

    /**
     * {@inheritdoc}
     */
    public function applyScript(array $id)
    {
        return <<<EOT
        (function() {
            function init(name) {
                var lat = $('#{$id['lat']}');
                var lng = $('#{$id['lng']}');

                var center = new qq.maps.LatLng(lat.val(), lng.val());

                var container = document.getElementById("map_"+name);
                var map = new qq.maps.Map(container, {
                    center: center,
                    zoom: {$this->getParams('zoom')}
                });

                var marker = new qq.maps.Marker({
                    position: center,
                    draggable: true,
                    map: map
                });

                if( ! lat.val() || ! lng.val()) {
                    var citylocation = new qq.maps.CityService({
                        complete : function(result){
                        map.setCenter(result.detail.latLng);
                        marker.setPosition(result.detail.latLng);
                    }
                    });

                    citylocation.searchLocalCity();
                }

                qq.maps.event.addListener(map, 'click', function(event) {
                    console.log("event.latLng",event.latLng)
                    marker.setPosition(event.latLng);
                });

                qq.maps.event.addListener(marker, 'position_changed', function(event) {
                    var position = marker.getPosition();
                    lat.val(position.getLat());
                    lng.val(position.getLng());
                });

                var ap = new qq.maps.place.Autocomplete(document.getElementById("search-{$id['lat']}{$id['lng']}"));
                var searchService = new qq.maps.SearchService({
                    map : map,
                    complete:function(results){
                        console.log("results",results)
                        var searchword=$("#"+"search-{$id['lat']}{$id['lng']}").val()
                        if(results.type === "CITY_LIST") {
                            searchService.setLocation(results.detail.cities[0].cityName);
                            searchService.search(searchword);
                            return;
                        }
                       var pois = results.detail.pois;
                       if(pois){
                            for(var i = 0,l = pois.length;i < l; i++){
                                if(pois[i].name==searchword){
                                    map.setCenter(pois[i].latLng);
                                    marker.setPosition(pois[i].latLng);
                                    lat.val(pois[i].latLng.getLat());
                                    lng.val(pois[i].latLng.getLng());
                                }
                            }
                       }
                    },
                });

                qq.maps.event.addListener(ap, "confirm", function(res){
                    searchService.search(res.value);
                });
            }

            init('{$id['lat']}{$id['lng']}');
        })();
EOT;
    }
}