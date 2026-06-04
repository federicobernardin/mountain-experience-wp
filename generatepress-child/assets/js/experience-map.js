(function () {
	'use strict';

	const routeBlocks = document.querySelectorAll('.me-route-map');

	if (!routeBlocks.length || typeof L === 'undefined' || typeof Chart === 'undefined') {
		return;
	}

	function haversineDistance(pointA, pointB) {
		const radius = 6371000;
		const lat1 = pointA.lat * Math.PI / 180;
		const lat2 = pointB.lat * Math.PI / 180;
		const deltaLat = (pointB.lat - pointA.lat) * Math.PI / 180;
		const deltaLng = (pointB.lng - pointA.lng) * Math.PI / 180;

		const a =
			Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
			Math.cos(lat1) * Math.cos(lat2) *
			Math.sin(deltaLng / 2) * Math.sin(deltaLng / 2);

		const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

		return radius * c;
	}

	function parseGpx(gpxText) {
		const parser = new DOMParser();
		const xml = parser.parseFromString(gpxText, 'application/xml');
		const parserError = xml.querySelector('parsererror');

		if (parserError) {
			throw new Error('Invalid GPX file.');
		}

		const nodes = Array.from(xml.querySelectorAll('trkpt'));

		if (!nodes.length) {
			throw new Error('No track points found.');
		}

		let cumulativeDistance = 0;
		let previousPoint = null;

		return nodes.map((node) => {
			const lat = parseFloat(node.getAttribute('lat'));
			const lng = parseFloat(node.getAttribute('lon'));
			const eleNode = node.querySelector('ele');
			const elevation = eleNode ? parseFloat(eleNode.textContent) : null;

			const point = {
				lat,
				lng,
				elevation: Number.isFinite(elevation) ? elevation : null,
				distance: cumulativeDistance
			};

			if (previousPoint) {
				cumulativeDistance += haversineDistance(previousPoint, point);
				point.distance = cumulativeDistance;
			}

			previousPoint = point;

			return point;
		}).filter((point) => Number.isFinite(point.lat) && Number.isFinite(point.lng));
	}

	function getClosestIndex(points, distanceKm) {
		const distanceMeters = distanceKm * 1000;
		let closestIndex = 0;
		let closestDifference = Infinity;

		points.forEach((point, index) => {
			const difference = Math.abs(point.distance - distanceMeters);

			if (difference < closestDifference) {
				closestDifference = difference;
				closestIndex = index;
			}
		});

		return closestIndex;
	}

	const verticalProfileLine = {
		id: 'verticalProfileLine',
		afterDatasetsDraw: function (chart) {
			const activeElements = chart.getActiveElements();

			if (!activeElements.length) {
				return;
			}

			const activeElement = activeElements[0];
			const meta = chart.getDatasetMeta(activeElement.datasetIndex);

			if (!meta || !meta.data || !meta.data[activeElement.index]) {
				return;
			}

			const point = meta.data[activeElement.index];
			const ctx = chart.ctx;
			const topY = chart.chartArea.top;
			const bottomY = chart.chartArea.bottom;

			ctx.save();
			ctx.beginPath();
			ctx.moveTo(point.x, topY);
			ctx.lineTo(point.x, bottomY);
			ctx.lineWidth = 2;
			ctx.strokeStyle = 'rgba(183, 93, 50, 0.95)';
			ctx.setLineDash([6, 6]);
			ctx.stroke();
			ctx.restore();
		}
	};

	if (Chart && typeof Chart.register === 'function') {
		Chart.register(verticalProfileLine);
	}

	function initRouteBlock(block) {
		const gpxUrl = block.dataset.gpxUrl;
		const distanceLabel = block.dataset.distanceLabel || 'Distance';
		const elevationLabel = block.dataset.elevationLabel || 'Elevation';
		const errorLabel = block.dataset.errorLabel || 'GPX track could not be loaded.';
		const mapElement = block.querySelector('.me-route-map__canvas');
		const chartElement = block.querySelector('.me-route-profile__chart');

		if (!gpxUrl || !mapElement || !chartElement) {
			return;
		}

		const map = L.map(mapElement, {
			scrollWheelZoom: false,
			zoomControl: true
		});

		L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
	maxZoom: 17,
	attribution: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap'
}).addTo(map);

		fetch(gpxUrl)
			.then((response) => {
				if (!response.ok) {
					throw new Error('Unable to fetch GPX.');
				}

				return response.text();
			})
			.then((gpxText) => {
				const points = parseGpx(gpxText);

				if (!points.length) {
					throw new Error('No valid points.');
				}

				const latLngs = points.map((point) => [point.lat, point.lng]);

				/*
				 * White halo below the route.
				 * It is added first, so the green route naturally stays above it.
				 */
				L.polyline(latLngs, {
					color: '#ffffff',
					weight: 9,
					opacity: 0.72,
					lineJoin: 'round',
					lineCap: 'round',
					interactive: false
				}).addTo(map);

				const routeLine = L.polyline(latLngs, {
					color: '#c0392b',
					weight: 5,
					opacity: 0.96,
					lineJoin: 'round',
					lineCap: 'round'
				}).addTo(map);

				map.fitBounds(routeLine.getBounds(), {
					padding: [28, 28]
				});

				const marker = L.circleMarker(latLngs[0], {
					radius: 8,
					color: '#ffffff',
					weight: 3,
					fillColor: '#c0392b',
					fillOpacity: 1
				}).addTo(map);

				const elevationPoints = points.filter((point) => point.elevation !== null);

				if (!elevationPoints.length) {
					block.classList.add('has-no-elevation');
					return;
				}

				const labels = elevationPoints.map((point) => (point.distance / 1000).toFixed(2));
				const data = elevationPoints.map((point) => Math.round(point.elevation));

				const chart = new Chart(chartElement, {
					type: 'line',
					data: {
						labels,
						datasets: [
							{
								label: elevationLabel,
								data,
								borderColor: '#244536',
								backgroundColor: 'rgba(36, 69, 54, 0.14)',
								borderWidth: 2,
								pointRadius: 0,
								pointHoverRadius: 5,
								pointHoverBackgroundColor: '#b75d32',
								pointHoverBorderColor: '#ffffff',
								pointHoverBorderWidth: 3,
								tension: 0.28,
								fill: true
							}
						]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						interaction: {
							mode: 'index',
							intersect: false
						},
						plugins: {
							legend: {
								display: false
							},
							tooltip: {
								displayColors: false,
								callbacks: {
									title: function (items) {
										if (!items.length) {
											return '';
										}

										return distanceLabel + ': ' + items[0].label + ' km';
									},
									label: function (item) {
										return elevationLabel + ': ' + item.formattedValue + ' m';
									}
								}
							}
						},
						scales: {
							x: {
								title: {
									display: true,
									text: distanceLabel + ' (km)'
								},
								grid: {
									display: false
								},
								ticks: {
									maxTicksLimit: 8
								}
							},
							y: {
								title: {
									display: true,
									text: elevationLabel + ' (m)'
								},
								grid: {
									color: 'rgba(24, 34, 29, 0.10)'
								}
							}
						},
						onHover: function (event, activeElements) {
							if (!activeElements.length) {
								return;
							}

							const activeIndex = activeElements[0].index;
							const distanceKm = parseFloat(labels[activeIndex]);
							const closestIndex = getClosestIndex(points, distanceKm);
							const point = points[closestIndex];

							marker.setLatLng([point.lat, point.lng]);
						}
					}
				});

				chartElement.addEventListener('touchmove', function (event) {
					if (!event.touches.length) {
						return;
					}

					const touch = event.touches[0];
					const rect = chartElement.getBoundingClientRect();
					const x = touch.clientX - rect.left;
					const chartArea = chart.chartArea;

					if (!chartArea || x < chartArea.left || x > chartArea.right) {
						return;
					}

					const ratio = (x - chartArea.left) / (chartArea.right - chartArea.left);
					const index = Math.max(
						0,
						Math.min(
							elevationPoints.length - 1,
							Math.round(ratio * (elevationPoints.length - 1))
						)
					);

					const point = elevationPoints[index];

					marker.setLatLng([point.lat, point.lng]);

					chart.setActiveElements([
						{
							datasetIndex: 0,
							index
						}
					]);

					chart.tooltip.setActiveElements(
						[
							{
								datasetIndex: 0,
								index
							}
						],
						{
							x: x,
							y: chartArea.top
						}
					);

					chart.update();

					event.preventDefault();
				}, {
					passive: false
				});
			})
			.catch((error) => {
				console.error('Mountain Experience GPX error:', error);

				block.classList.add('has-error');
				block.insertAdjacentHTML(
					'beforeend',
					'<p class="me-route-map__error">' + errorLabel + '</p>'
				);
			});
	}

	routeBlocks.forEach(initRouteBlock);
}());