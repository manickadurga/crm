import {
    AutoComplete,
    Button,
    Cascader,
    Checkbox,
    Col,
    Form,
    Input,
    InputNumber,
    Row,
    Tag,
    Select, Space,
    Alert,
    DatePicker,
    Dropdown,
    Steps, Image,
    Flex, Upload, message
  } from 'antd';
  import React, { useState, useEffect } from 'react';
  import { SearchOutlined, UploadOutlined, LoadingOutlined, RightCircleOutlined, LeftCircleOutlined } from "@ant-design/icons";
  // import dummyImg from "../../../../public/assests/img/noprofile.png"
  import dummyImg from '../../../../../public/assests/img/noprofile.png'
  // import DoubleFieldComponent from '../../Components/DoubleFieldComponent';
  import DoubleFieldComponent from '../../../Components/DoubleFieldComponent'
  // import LeafletMap from '../../Components/LeafletMap';
  import LeafletMap from '../../../Components/LeafletMap'
  // import { getCustomers, getCustomerById, getFormfields } from '../../API';
  import {  getLeadById, getFormfields } from '../../../API';
  import axios from 'axios';
  import { useParams, useNavigate, Link } from 'react-router-dom';
  const { Option } = Select;
  
  const formItemLayout = {
    labelCol: {
      xs: { span: 28 },
      sm: { span: 8 },
    },
    wrapperCol: {
      xs: { span: 24 },
      sm: { span: 16 },
    },
  };
  
  const gridStyle = {
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
    gap: '16px',
  };
  const fullgridStyle = {
    display: 'grid',
    gridTemplateColumns: '1fr',
  };
  
  
  
  const LeadsForm = () => {
    const [form] = Form.useForm();
    const [current, setCurrent] = useState(0);
    const [formData, setFormData] = useState([]);
    const [isSubmitted, setIsSubmitted] = useState(false);
    const [selectedTags, setSelectedTags] = useState({});
    // const [formCustomers, setFormCustomers] = useState([]);  
    const [leadFormFields, setLeadFormFields] = useState([]);
    const [lead, setLead] = useState([])
    const [fileList, setFileList] = useState([]);
    // const [formFields, setFormFields] = useState([]);
    const [imgUrl, setImgUrl] = useState(''); // Start with an empty image URL
  
    // const [imgUrl, setImgUrl] = useState(fields.imgUrl || dummyImg);
    const { id } = useParams();
  
    const navigate = useNavigate();
  
    useEffect(() => {
      getFormfields('Leads')
        .then((res) => {
          setLeadFormFields(res);
        })
        .catch((error) => {
          console.error("Error fetching form fields:", error);
        });
    }, []);
  
    useEffect(() => {
      console.log("leadformfields state has been set:", leadFormFields);
    }, [leadFormFields]);
  
    useEffect(() => {
      const fetchleadData = async () => {
        try {
          const response = await getLeadById(id); // Fetch customer by ID
          setLead(response.leads); 
          console.log('updateleads',response)
          // Assuming response includes customer data
        } catch (error) {
          console.error("Error fetching lead details:", error);
        }
      };
  
      fetchleadData();
    }, [id]);
    useEffect(() => {
      console.log("update state has been set:", lead);
    }, [lead]);
  
  
    useEffect(() => {
      if (lead) {
        const leadData = {};
        Object.keys(lead).forEach(key => {
          leadData[key] = lead[key];
        });
        form.setFieldsValue(leadData);
        console.log('leads update',leadData )
      }
    }, [lead, form]);
  
    
  
    const convertToPNG = async (imageUrl) => {
      const img = document.createElement('img');
      img.crossOrigin = 'Anonymous';
  
      return new Promise((resolve, reject) => {
        img.onload = () => {
          const canvas = document.createElement('canvas');
          canvas.width = img.width;
          canvas.height = img.height;
  
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0);
  
          canvas.toBlob(blob => {
            const reader = new FileReader();
            reader.onloadend = () => {
              resolve(reader.result);
            };
            reader.readAsDataURL(blob);
          }, 'image/png');
        };
  
        img.onerror = () => {
          reject(new Error('Failed to load image'));
        };
  
        img.src = imageUrl;
      });
    };
  
    const handleChange = async (info) => {
      let newFileList = [...info.fileList];
      newFileList = newFileList.slice(-1);
  
      if (newFileList.length > 0) {
        const file = newFileList[0];
        if (file.originFileObj) {
          // Check file size (maxSize in KB)
          const maxSize = 2048; // 2048 KB = 2 MB
          if (file.originFileObj.size / 1024 > maxSize) {
            message.error('Image must be smaller than 2MB!');
            return;
          }
  
          const reader = new FileReader();
          reader.onload = async (e) => {
            try {
              const convertedImage = await convertToPNG(e.target.result);
              setImgUrl(convertedImage);
              // Optionally set form field value (if using Form from antd or other form management)
              // form.setFieldsValue({ image: convertedImage });
            } catch (error) {
              console.error('Error converting image:', error);
              setImgUrl(dummyImg); // Reset to dummy image on error
            }
          };
          reader.readAsDataURL(file.originFileObj);
        }
      } else {
        setImgUrl(dummyImg);
      }
  
      setFileList(newFileList);
    };
    const handleSelectChange = (value, field) => {
      const updatedFormData = { ...formData, [field.name]: value };
      leadFormFields.forEach(section => {
        section.fields.forEach(f => {
          if (f.depends && f.depends === field.name) {
            updatedFormData[f.name] = null;
          }
        });
      });
      setFormData(updatedFormData);
      form.setFieldsValue(updatedFormData);
    };
  
    const handleTagChange = (value, fieldName) => {
      setSelectedTags((prevState) => ({
        ...prevState,
        [fieldName]: value,
      }));
  
      setFormData((prevState) => ({
        ...prevState,
        [fieldName]: value,
      }));
  
      form.setFieldsValue({
        ...form.getFieldsValue(),
        [fieldName]: value,
      });
    };
  
    const handleLocationChange = (location) => {
      const updatedFormData = { ...formData, location };
      setFormData(updatedFormData);
      form.setFieldsValue(updatedFormData);
    };
  
    const storeLeadsData = async (data) => {
      try {
        const response = await axios.post('http://127.0.0.1:8001/leads', data);
        return response.data;
      } catch (error) {
        console.error('Error storing lead data:', error);
        throw error;
      }
    };
    const updateLeadsData = async (id, data) => {
      try {
        const response = await axios.put(`http://127.0.0.1:8000/leads/${id}`, data);
        return response.data;
      } catch (error) {
        console.error('Error updating lead data:', error);
        throw error;
      }
    };
  
  
    const onFinish = async (values) => {
      const formDataValues = { ...formData, ...values };
      if (id) {
        formDataValues.id = id;
      }
      try {
        if (id) {
          await updateLeadsData(id, formDataValues);
        } else {
          await storeLeadsData(formDataValues);
        }
        message.success(id ? 'Leads Details Updated Successfully' : 'Leads Details Added Successfully');
      
        navigate('/leads'); 
      } catch (error) {
        console.error('Error handling form submission:', error);
        if (error.response) {
          message.error('Failed to handle form submission. Server responded with status');
        } else {
          message.error('Failed to handle form submission. Please try again later.');
        }
      }
    };
  
    const steps = leadFormFields.map((section, sectionIndex) => ({
      title: section.blockname,
      content: (
        <div key={section.blockname} id={section.blockname.toLowerCase().replace(/\s+/g, '_')}>
          <h3>{section.blockname}:</h3>
          <div style={section.fields.length === 1 ? fullgridStyle : gridStyle} className={section.blockname.toLowerCase().replace(/\s+/g, '_')}>
            {section.fields.map((field, fieldIndex) => (
              <div key={fieldIndex} style={{ width: '100%' }} className={section.blockname.toLowerCase().replace(/\s+/g, '_') + '_box'}>
                <Form.Item
                  name={field.name}
                  label={field.label}
                  rules={Array.isArray(field.rules) ? field.rules : [field.rules]} // Ensure rules is an array
                  className="form-item"
                >
                  {field.type === 'dropdown' ? (
                    <Select style={{ width: '100%' }} defaultValue={field.defaultValue}
                      onChange={(value) => handleSelectChange(value, field)}
                    >
                      {field.options.map((option, optionIndex) => (
                        <Select.Option key={optionIndex} value={option.value}>
                          {option.label}
                        </Select.Option>
                      ))}
                    </Select>
                  ) : field.type === 'datepicker' ? (
                    <DatePicker style={{ width: '100%' }} />
                  ) : field.type === 'checkbox' ? (
                    <Checkbox defaultChecked={field.value || field.defaultValue}></Checkbox>
                  ) : field.prefixDropdown ? (
                    <Input
                      addonBefore={
                        <Select
                          defaultValue={field.prefixOptionsValue || field.prefixOptions[0].value}
                          onChange={(value) => handleSelectChange(value, { ...field, name: field.name + '_prefix' })}
                        >
                          {field.prefixOptions.map((option, optionIndex) => (
                            <Select.Option key={optionIndex} value={option.value}>
                              {option.label}
                            </Select.Option>
                          ))}
                        </Select>
                      }
                      style={{ width: '100%' }}
                      defaultValue={field.value || field.defaultValue}
                      onChange={(e) => handleSelectChange(e.target.value, field)}
                    />
                  ) : field.suffixDropdown ? (
                    <Input
                      addonAfter={
                        <Select
                          defaultValue={field.suffixOptionsValue || field.suffixOptions[0].value}
                          onChange={(value) => handleSelectChange(value, { ...field, name: field.name + '_suffix' })}
                        >
                          {field.suffixOptions.map((option, optionIndex) => (
                            <Select.Option key={optionIndex} value={option.value}>
                              {option.label}
                            </Select.Option>
                          ))}
                        </Select>
                      }
                      style={{ width: '100%' }}
                      defaultValue={field.value || field.defaultValue}
                      onChange={(e) => handleSelectChange(e.target.value, field)}
                    />
                  ) : field.type === 'textarea' ? (
                    <Input.TextArea />
                  ) : field.type === '69' ? (
                    <Upload
                      listType="picture"
                      maxCount={1}
                      fileList={fileList}
                      onChange={handleChange}
                      beforeUpload={() => false} // Prevent auto-upload
                      showUploadList={false}
                    >
                      <img
                        src={imgUrl || dummyImg}
                        alt="image"
                        style={{
                          width: 200,
                          height: 200,
                          borderRadius: 10,
                        }}
                      />
                      <Button type="button" icon={<UploadOutlined />}>Click to Upload</Button>
                    </Upload>
  
  
                  ) : field.type === '8' ? (
                    id && lead.location ? (
                      <div>
                        {/* <strong>Location:</strong> */}
                        <LeafletMap
                          onLocationChange={handleLocationChange}
                          defaultValues={{
                            lat: lead.location.lat,
                            lng: lead.location.lng,
                            address: lead.location.address,
                            city: lead.location.city,
                            country: lead.location.country,
                            postcode: lead.location.zipcode
                          }}
                        />
  
                       </div> 
                    ) : (
                      <LeafletMap
                        onLocationChange={handleLocationChange}
                        defaultValues={{
                          lat: field.lat,
                          lng: field.lng,
                          address: field.address,
                          city: field.city,
                          country: field.country,
                          postcode: field.zipcode
                        }}
                      />
                    )
                  ) :
                    field.type === '33' ? (
                      <Select
                        mode="multiple"
                        style={{ width: '100%' }}
                        placeholder="Select projects"
                        defaultValue={field.value}
                        options={field.options.map((option, index) => ({
                          label: (
                            <Space key={index}>
                              <span
                                aria-label={option.label}
                                className={`badge-${option.value}`}
                                style={{ display: 'block', width: '14px', height: '14px', borderRadius: '50%' }}
                              ></span>
                              {option.label}
                            </Space>
                          ),
                          value: option.value,
                        }))}
                      />
                    ) : field.type === '16' ? (
                      <Select
                        style={{ width: '100%' }}
                        placeholder="Select Contact Type"
                        defaultValue={field.value}
                        options={field.options.map((option, index) => ({
                          label: option.label,
                          value: option.value,
                        }))}
                      />
                    ) : field.type === '33' ? (
                      <Select
                        mode="multiple"
                        style={{ width: '100%' }}
                        placeholder="Select Tags"
                        defaultValue={field.value}
                        options={field.options.map((option, index) => ({
                          label: (
                            <Space key={index}>
                              <span
                                aria-label={option.label}
                                className={`badge-${option.value}`}
                                style={{ display: 'block', width: '14px', height: '14px', borderRadius: '50%' }}
                              ></span>
                              {option.label}
                            </Space>
                          ),
                          value: option.value,
                        }))}
                      />
                    ) : field.type === 'doublefield' ? (
                      <DoubleFieldComponent fields={field.fields} />
                    ) : (
                      <Input type={field.type} style={{ width: '100%' }} defaultValue={field.defaultValue} />
                    )}
                </Form.Item>
              </div>
            ))}
          </div>
        </div>
      ),
    }));
  
  
    const next = async () => {
      try {
        // Validate all fields
        const values = await form.validateFields();
  
        leadFormFields[current].fields.forEach(field => {
          if (field.prefixDropdown) {
            values[field.name + '_prefix'] = formData[field.name + '_prefix'];
          }
          if (field.suffixDropdown) {
            values[field.name + '_suffix'] = formData[field.name + '_suffix'];
          }
        });
  
        // Check if any mandatory fields are empty
        const mandatoryFields = leadFormFields[current].fields.filter(field =>
          field.rules && Array.isArray(field.rules) && field.rules.some(rule => rule.required)
        );
  
        const emptyMandatoryFields = mandatoryFields.filter(field => {
          const value = values[field.name];
          const hasDefaultValue = field.hasOwnProperty('defaultValue');
          const hasValue = field.hasOwnProperty('value');
          return !value && !hasDefaultValue && !hasValue;
        });
  
        if (emptyMandatoryFields.length > 0) {
          // Handle case where mandatory fields are empty
          console.log("Mandatory fields are empty:", emptyMandatoryFields);
        } else {
          const updatedFormData = { ...formData, ...values };
          setFormData(updatedFormData);
          console.log("Submitting form data:", values);
  
          if (current === leadFormFields.length - 1) {
            // If it's the last step, store the customer data
            await storeLeadsData(updatedFormData);
            console.log("Leads data stored successfully");
          } else {
            // Move to the next step
            setCurrent(current + 1);
          }
        }
      } catch (err) {
        console.log("Validation error:", err);
      }
    };


    const prev = () => {
      setCurrent(current - 1);
    };
  
    // const [form] = Form.useForm();
  
    const [autoCompleteResult, setAutoCompleteResult] = useState([]);
  
    const onWebsiteChange = (value) => {
      if (!value) {
        setAutoCompleteResult([]);
      } else {
        setAutoCompleteResult(['.com', '.org', '.net', '.ai', '.in', '.co.in'].map((domain) => `${value}${domain}`));
      }
    };
  
    const websiteOptions = autoCompleteResult.map((website) => ({
      label: website,
      value: website,
    }));
  
  
    return (
      <div>
        <Form
          {...formItemLayout}
          form={form}
          name="leadform"
          onFinish={onFinish}
          scrollToFirstError
        >
          <Steps current={current} items={steps} size="small" title="oneee" />
          <div>{steps[current]?.content}</div> {/* Ensure steps[current]?.content is accessed safely */}
          <div style={{ marginTop: 24, display: 'flex', gap: '5px' }}>
            {current < steps.length - 0 && (
              <Link to="/leads">
                <Button type="primary" htmlType="button" style={{ marginLeft: '10px', marginRight: '10px' }}>
                  Cancel
                </Button>
              </Link>
            )}
            {current > 0 && (
              <Button style={{ margin: '0 8px' }} onClick={prev} icon={<LeftCircleOutlined />} disabled={current === 0}>
                Previous
              </Button>
            )}
  
            {current < steps.length - 1 && (
              <Button type="primary" onClick={next} icon={<RightCircleOutlined />} iconPosition="right">
                Next &nbsp;
              </Button>
            )}
  
            {current === steps.length - 1 && (
              <Button type="primary" htmlType="submit">
                {id ? 'Update' : 'Add'}
              </Button>
            )}
          </div>
        </Form>
      </div>
    );
  };
  
  export default LeadsForm;
  