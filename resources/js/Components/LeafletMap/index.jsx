import React, { useState, useEffect, useRef } from 'react';
import { Form, Input, Button, Row, Col, Select, Popover } from 'antd';
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import axios from 'axios';
import { getNames } from 'country-list';

const { Option } = Select;

const markerIcon = new L.Icon({
  iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
});



const LeafletMap = ({ onLocationChange, defaultValues }) => {
  const [markerPosition, setMarkerPosition] = useState({
    lat: parseFloat(defaultValues?.lat) || 24.186847428521244,
    lng: parseFloat(defaultValues?.lng) || 76.68896423093015,
  });
// const LeafletMap = ({ onLocationChange, defaultValues }) => {
//     const [markerPosition, setMarkerPosition] = useState({
//       lat: parseFloat(defaultValues.lat) || '',
//       lng: parseFloat(defaultValues.lng) || '',
//     });
  
    const [address, setAddress] = useState({
      country: defaultValues.country || '',
      city: defaultValues.city || '',
      postcode: defaultValues.postcode || '',
      address: defaultValues.address || '',
      lat: defaultValues.lat || '',
      lng: defaultValues.lng || '',
    });
  
    const handleAddressChange = (e) => {
      const { name, value } = e.target;
      setAddress((prevState) => ({ ...prevState, [name]: value }));
    };
  
    // const handleCountryChange = (value) => {
    //   setAddress((prevState) => ({ ...prevState, country: value }));
    //   axios.get(`https://nominatim.openstreetmap.org/search?country=${value}&format=json`)
    //     .then(response => {
    //       if (response.data && response.data.length > 0) {
    //         const location = response.data[0];
    //         setMarkerPosition({ lat: parseFloat(location.lat), lng: parseFloat(location.lon) });
    //         setAddress((prevState) => ({
    //           ...prevState,
    //           lat: parseFloat(location.lat),
    //           lng: parseFloat(location.lon),
    //         }));
    //         onLocationChange({
    //           country: value,
    //           city: address.city,
    //           postcode: address.postcode,
    //           address: address.address,
    //           lat: parseFloat(location.lat),
    //           lng: parseFloat(location.lon),
    //         });
    //       }
    //     });
    // };

    const handleCountryChange = (value) => {
      setAddress((prevState) => ({ ...prevState, country: value }));
    
      // Assuming `onLocationChange` is a prop passed down correctly
      axios.get(`https://nominatim.openstreetmap.org/search?country=${value}&format=json`)
        .then(response => {
          if (response.data && response.data.length > 0) {
            const location = response.data[0];
            
            // Update state with latitude and longitude
            setMarkerPosition({ lat: parseFloat(location.lat), lng: parseFloat(location.lon) });
            
            // Update address state with latitude and longitude
            setAddress((prevState) => ({
              ...prevState,
              lat: parseFloat(location.lat),
              lng: parseFloat(location.lon),
            }));
    
            // Call `onLocationChange` prop with updated location data
            if (typeof onLocationChange === 'function') {
              onLocationChange({
                country: value,
                city: address.city,
                postcode: address.postcode,
                address: address.address,
                lat: parseFloat(location.lat),
                lng: parseFloat(location.lon),
              });
            }
          }
        })
        .catch(error => {
          console.error('Error fetching location data:', error);
        });
    };
    
  
    const handleLatLngChange = (e) => {
      const { name, value } = e.target;
      const floatVal = parseFloat(value);
      if (!isNaN(floatVal)) {
        setAddress((prevState) => ({ ...prevState, [name]: floatVal }));
        setMarkerPosition((prevState) => ({
          ...prevState,
          [name === 'lat' ? 'lat' : 'lng']: floatVal,
        }));
        onLocationChange({
          ...address,
          [name]: floatVal,
        });
      }
    };
  
    const handleGeocode = (lat, lng) => {
      axios.get(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
        .then(response => {
          console.log(response);
          if (response.data) {
            const location = response.data.address;
            console.log('location',location)
            const updatedAddress = {
              country: location.country || '',
              city: location.city || location.town || location.village || '',
              postcode: location.postcode || '',
              address: response.data.display_name,
              lat,
              lng,
            };
            setAddress(updatedAddress);
            onLocationChange(updatedAddress);
          }
        });
    };
  
    const LocationMarker = () => {
      const map = useMapEvents({
        click(e) {
          setMarkerPosition(e.latlng);
          setAddress((prevState) => ({
            ...prevState,
            lat: e.latlng.lat,
            lng: e.latlng.lng,
          }));
          handleGeocode(e.latlng.lat, e.latlng.lng);
          map.setView(e.latlng, map.getZoom());
        },
      });
  
      return markerPosition === null ? null : (
        <Marker position={markerPosition} icon={markerIcon}>
          <Popover>{`Lat: ${markerPosition.lat}, Lng: ${markerPosition.lng}`}</Popover>
        </Marker>
      );
    };
  
    return (
      <div style={{display:'flex', minWidth:'100%', gap:'10%'}}>
        <div style={{display:'flex', flexDirection:'column', gap:'8px', width:'50%', marginRight:'1px'}}>
          <Select
            showSearch
            value={address.country || undefined}
            placeholder="Select Country"
            onChange={handleCountryChange}
            filterOption={(input, option) =>
              option.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
            }
          >
            {getNames().map((country) => (
              <Option key={country} value={country}>
                {country}
              </Option>
            ))}
          </Select>
          <Input name="city" value={address.city || address.state_district} onChange={handleAddressChange} placeholder='City'/>
          <Input name="postcode" value={address.postcode} onChange={handleAddressChange} placeholder='Post Code'/>
          <Input name="address" value={address.address} onChange={handleAddressChange} placeholder='Address'/>
          <h3 style={{marginBottom:0}}>Co-Ordinates :</h3>
          <Input
            name="lat"
            value={address.lat}
            onChange={handleLatLngChange}
            onKeyPress={(event) => {
              if (!/^[0-9.\-]*$/.test(event.key)) {
                event.preventDefault();
              }
            }}
            placeholder='Latitude'
          />
          <Input
            name="lng"
            value={address.lng}
            onChange={handleLatLngChange}
            onKeyPress={(event) => {
              if (!/^[0-9.\-]*$/.test(event.key)) {
                event.preventDefault();
              }
            }}
            placeholder='Longitude'
          />
        </div>
        <div style={{ height: '400px', width: '100%' }}>
          <MapContainer
            center={[markerPosition.lat, markerPosition.lng]}
            zoom={10}
            style={{ height: '100%', width: '100%', borderRadius:'10px' }}
            key={`${markerPosition.lat}-${markerPosition.lng}`} // Use key to force re-render
          >
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            <LocationMarker />
          </MapContainer>
        </div>
      </div>
    );
  };
  
  export default LeafletMap;
