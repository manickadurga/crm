import React, { useState, useEffect, useRef } from 'react';
import { Form, Input, Button, Row, Col, Select } from 'antd';
import { MapContainer, TileLayer, Marker, useMap } from 'react-leaflet';
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
    lat: parseFloat(defaultValues.lat) || -3.745,
    lng: parseFloat(defaultValues.lng) || -38.523,
  });
  const [center, setCenter] = useState({
    lat: parseFloat(defaultValues.lat) || -3.745,
    lng: parseFloat(defaultValues.lng) || -38.523,
  });
  const [address, setAddress] = useState({
    country: defaultValues.country || '',
    city: defaultValues.city || '',
    postcode: defaultValues.postcode || '',
    address: defaultValues.address || '',
    lat: defaultValues.lat || -3.745,
    lng: defaultValues.lng || -38.523,
  });

  const handleAddressChange = (e) => {
    const { name, value } = e.target;
    setAddress((prevState) => ({ ...prevState, [name]: value }));
  };

  const handleCountryChange = (value) => {
    setAddress((prevState) => ({ ...prevState, country: value }));
    axios.get(`https://nominatim.openstreetmap.org/search?country=${value}&format=json`)
      .then(response => {
        if (response.data && response.data.length > 0) {
          const location = response.data[0];
          const lat = parseFloat(location.lat);
          const lng = parseFloat(location.lon);
          setMarkerPosition({ lat, lng });
          setCenter({ lat, lng });
          setAddress((prevState) => ({
            ...prevState,
            lat,
            lng,
          }));
          onLocationChange({
            country: value,
            city: address.city,
            postcode: address.postcode,
            address: address.address,
            lat,
            lng,
          });
        }
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
      setCenter((prevState) => ({
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
        if (response.data) {
          const location = response.data.address;
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

  useEffect(() => {
    if (address.lat && address.lng) {
      handleGeocode(address.lat, address.lng);
    }
  }, [address.lat, address.lng]);

  const LocationMarker = () => {
    const map = useMap();
    useEffect(() => {
      map.setView(position, map.getZoom());
    }, [position, map]);
    return (
      <Marker position={markerPosition} icon={markerIcon}></Marker>
    );
  };

  return (
    <>
      <div>
        <Select
            showSearch
            value={address.country || 'None'}
            placeholder="Select a country"
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
          <Input name="city" value={address.city} onChange={handleAddressChange} />
          <Input name="postcode" value={address.postcode} onChange={handleAddressChange} />
          <Input name="address" value={address.address} onChange={handleAddressChange} />
          <Input name="lat" value={address.lat} onChange={handleLatLngChange} />
          <Input name="lng" value={address.lng} onChange={handleLatLngChange} />
      </div>
      <div style={{ height: '400px', width: '100%' }}>
            <MapContainer
              center={center}
              zoom={10}
              style={{ height: '100%', width: '100%' }}
            >
              <TileLayer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
              />
              <LocationMarker />
            </MapContainer>
      </div>
    </>
  );
};

export default LeafletMap;
