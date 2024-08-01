import React, { useContext } from "react";
import axios from "axios";
import { message } from "antd";
import { useNavigate } from "react-router-dom";
import { DataContext } from "../../Context/Context";

const Submit = ({ basePath, id }) => {
  const { FormFieldsState, formData } = useContext(DataContext);
  const navigate = useNavigate();

  const onFinish = async (values) => {
    console.log('Received values from form:', values);
    
    // Combine form data with existing data
    const formDataValues = { ...formData, ...values };

    // Extract selected option IDs from form fields
    const selectedOptionIds = {};
    FormFieldsState.forEach(section => {
      section.fields.forEach(field => {
        if ((field.type === '16' || field.type === '33') && values[field.name]) {
          if (field.type === '16') {
            // For single-select fields (type '16')
            const selectedOption = field.options.find(option => option.label === values[field.name]);
            if (selectedOption) {
              selectedOptionIds[field.name] = selectedOption.id !== undefined ? selectedOption.id : values[field.name];
            }
          } else if (field.type === '33') {
            // For multi-select fields (type '33')
            const selectedOptions = field.options.filter(option => values[field.name].includes(option.label));
            const ids = selectedOptions.map(option => option.id);
            selectedOptionIds[field.name] = ids;
          }
        }
      });
    });

    // Prepare data to submit including selected option IDs
    const dataToSubmit = {
      ...formDataValues,
      ...selectedOptionIds,
    };
    console.log('Data to Submit:', dataToSubmit);

    // Determine URL and method
    const url = id ? `http://127.0.0.1:8001/api/${basePath}/${id}` : `http://127.0.0.1:8001/api/${basePath}`;
    const method = id ? 'put' : 'post';

    try {
      // Make HTTP request to submit data
      const response = await axios({ method, url, data: dataToSubmit });
      console.log(`${id ? 'Customer updated:' : 'Customer created:'}`, response.data);
      message.success(`${id ? 'Customer updated successfully!' : 'Customer created successfully!'}`);
      navigate(`/${basePath}`); // Navigate to list view or success page
    } catch (error) {
      console.error(`There was an error ${id ? 'updating' : 'creating'} the customer!`, error);
      message.error(`There was an error ${id ? 'updating' : 'creating'} the customer!`);
    }
  };

  return onFinish; // Return the onFinish function to use in Form component
};

export default Submit;

// utils.js
export const getSelectedOptionIds = (FormFieldsState, values) => {
    const selectedOptionIds = {};
    
    FormFieldsState.forEach(section => {
      section.fields.forEach(field => {
        if ((field.type === '16' || field.type === '33') && values[field.name]) {
          if (field.type === '16') {
            // For single-select fields (type '16')
            const selectedOption = field.options.find(option => option.label === values[field.name]);
            if (selectedOption) {
              selectedOptionIds[field.name] = selectedOption.id !== undefined ? selectedOption.id : values[field.name];
            }
          } else if (field.type === '33') {
            // For multi-select fields (type '33')
            const selectedOptions = field.options.filter(option => values[field.name].includes(option.label));
            const ids = selectedOptions.map(option => option.id);
            selectedOptionIds[field.name] = ids;
          }
        }
      });
    });
  
    return selectedOptionIds;
  };
  
