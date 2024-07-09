// import React, { useState, useEffect } from 'react';
// import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
// import 'leaflet/dist/leaflet.css';
// import L from 'leaflet';
// import axios from 'axios';

// const markerIcon = new L.Icon({
//   iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
//   iconSize: [25, 41],
//   iconAnchor: [12, 41],
// });

// const ViewMap = ({ defaultValues }) => {
//   const [markerPosition, setMarkerPosition] = useState({
//     lat: parseFloat(defaultValues?.lat) || 24.186847428521244,
//     lng: parseFloat(defaultValues?.lng) || 76.68896423093015,
//   });

//   useEffect(() => {
//     if (defaultValues) {
//       setMarkerPosition({
//         lat: parseFloat(defaultValues.lat) || 24.186847428521244,
//         lng: parseFloat(defaultValues.lng) || 76.68896423093015,
//       });
//     }
//   }, [defaultValues]);

//   const LocationMarker = () => {
//     useMapEvents({
//       click(e) {
//         setMarkerPosition(e.latlng);
//       },
//     });

//     return (
//       <Marker position={markerPosition} icon={markerIcon}>
//         <Popup>{`Lat: ${markerPosition.lat}, Lng: ${markerPosition.lng}`}</Popup>
//       </Marker>
//     );
//   };

//   return (
//     <MapContainer
//       center={[markerPosition.lat, markerPosition.lng]}
//       zoom={13}
//       style={{ height: '400px', width: '100%', borderRadius: '10px' }}
//     >
//       <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
//       <LocationMarker />
//     </MapContainer>
//   );
// };

// export default ViewMap;
import React, { useState, useEffect } from 'react';
import { MapContainer, TileLayer, Marker, useMapEvents, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const markerIcon = new L.Icon({
  iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
});

const ViewMap = ({ defaultValues }) => {
  const [markerPosition, setMarkerPosition] = useState({
    lat: parseFloat(defaultValues?.lat) || 24.186847428521244,
    lng: parseFloat(defaultValues?.lng) || 76.68896423093015,
  });

  useEffect(() => {
    if (defaultValues) {
      setMarkerPosition({
        lat: parseFloat(defaultValues.lat) || 24.186847428521244,
        lng: parseFloat(defaultValues.lng) || 76.68896423093015,
      });
    }
  }, [defaultValues]);

  const LocationMarker = () => {
    useMapEvents({
      click(e) {
        setMarkerPosition(e.latlng);
      },
    });

    return (
      <Marker position={markerPosition} icon={markerIcon}>
        <Popup>{`Lat: ${markerPosition.lat}, Lng: ${markerPosition.lng}`}</Popup>
      </Marker>
    );
  };

  return (
    <MapContainer
      center={[markerPosition.lat, markerPosition.lng]}
      zoom={13}
      style={{ height: '400px', width: '100%', borderRadius: '10px' }}
    >
      <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
      <LocationMarker />
    </MapContainer>
  );
};

export default ViewMap;
