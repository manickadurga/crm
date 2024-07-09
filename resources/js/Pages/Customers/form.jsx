import {
    AutoComplete,
    Button,
    Cascader,
    Checkbox,
    Col,
    Form,
    Input,
    InputNumber,
    Row, Tag,
    Select, Space,
    Alert,
    DatePicker,
    Dropdown,
    Steps, Image,
    Flex, Upload, message
  } from 'antd';
  import React, { useState,useEffect } from 'react';
  import { SearchOutlined, UploadOutlined, LoadingOutlined } from "@ant-design/icons";
  import dummyImg from '../../../../public/assests/img/noprofile.png';
  import DoubleFieldComponent from '../../Components/DoubleFieldComponent';
  // import GoogleMapComponent from '../../Components/GoogleMap';
  import LeafletMap from '../../Components/LeafletMap';
  import { getCustomers } from '../../API';
  import axios from 'axios';
  const { Option } = Select;
  
  const formItemLayout = {
    labelCol: {
      xs: {
        span: 28,
      },
      sm: {
        span: 8,
      },
    },
    wrapperCol: {
      xs: {
        span: 24,
      },
      sm: {
        span: 16,
      },
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
  
  
  
  const formFields = [
    {
      title: "Contact Information",
      fields: [
        {
          name: 'image',
          label: 'Upload your IMG',
          type: 'avatar',
          // imgUrl: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png'
        },
        {
          name: 'name',
          label: 'Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Name!',
            },
          ],
        },
        {
          name: 'primary_email',
          label: 'Primary Email',
          rules: [{ required: false }],
          type: 'email',
          defaultValue: '',
        },
        {
          name: 'primary_phone',
          label: 'Primary Phone',
          rules: [
            { required: false },
            { pattern: /^[0-9]+$/, message: 'Phone number must be digits only' },
            { min: 10, message: 'Phone number must be at least 10 digits' },
            { max: 15, message: 'Phone number must be at most 15 digits' },
          ],
          type: 'text',
          defaultValue: '',
        },
        {
          name: 'website',
          label: 'Website',
          rules: [
            { required: false },
            { type: 'url', message: 'Please enter a valid URL' }
          ],
          type: 'url',
          defaultValue: '',
        },
        {
          name: 'fax',
          label: 'Fax',
          rules: [{ required: false }],
          type: 'text',
          defaultValue: '',
        },
        {
          name: 'fiscal_information',
          label: 'Fiscal Information',
          rules: [{ required: false }],
          type: 'text',
          defaultValue: '',
        },
        {
          name: "projects",
          label: "Projects",
          type: "tagfields",
          rules: [{ required: false }],
          projectOptions: [
            { label: 'Gauzy Platform (Open-Source)', value: 'Gauzy Platform (Open-Source)' },
            { label: 'Gauzy Website', value: 'Gauzy Website' },
            { label: 'Gauzy Platform SaaS', value: 'Gauzy Platform SaaS' },
            { label: 'Gauzy Platform DevOps', value: 'Gauzy Platform DevOps' },
          ]
        },
        {
          name: 'contact_type',
          label: 'Contact Type',
          rules: [{ required: false }],
          type: 'dropdown',
          options: [
            { label: 'CUSTOMER', value: 'customer' },
            { label: 'CLIENT', value: 'client' },
            { label: 'LEAD', value: 'lead' },
          ],
          value: 'customer'
        },
        {
          name: "tags",
          label: "Tags",
          type:"tagfield",
          tagOptions: [
            { label: 'VIP', value: 'vip' },
            { label: 'Urgent', value: 'urgent' },
            { label: 'Crazy', value: 'crazy' },
            { label: 'Broken', value: 'broken' },
            { label: 'TODO', value: 'todo' },
            { label: 'In Process', value: 'in Process' },
            { label: 'Verified', value: 'verified' },
            { label: 'Third Party API', value: 'third party api' },
            { label: 'Killer', value: 'killer' },
            { label: 'Idiot', value: 'idiot' },
            { label: 'Super', value: 'super' },
            { label: 'WIP', value: 'wip' },
            {label:'type:help wanted:pray:',value:'type:help wanted:pray:'},
            {label:'type:question:question:',value:'type:question:question:'},
            { label: 'bug', value: 'bug' },
            { label: 'priority:highest', value: 'priority:highest' },
            { label: 'enhancement', value: 'enhancement' },
            { label: 'type:enhancement', value: 'type:enhancement' },
            { label: 'Desktop Timer', value: 'desktop timer' },
            { label: 'Changes requested', value: 'changes requested' },
            { label: 'type:bug:bug:', value: 'type:bug:bug:' },
            { label: 'FIX', value: 'fix' },
            { label: 'UI', value: 'ui' },
            { label: 'priority:low', value: 'priority:low' },
            { label: 'type:devops', value: 'type:devops' },
            { label: 'type:performance:zap:', value: 'type:performance:zap:' },
          ],
        },
      ],
    },
    {
      title: "Address Information",
      fields: [
        {
          name: 'location',
          // label: 'My Map', #Dont Use Label
          type: 'location',
          // lat: '8.7150',
          // lng: '77.7656',
          // address:'253 Big Street',
          // country: 'India',
          // city:'Tirunelveli',
          // zipcode: '627006'
        },
  
      ]
    },
    {
      title: "Description Information",
      fields: [
        {
          name: 'type',
          label: 'Type',
          rules: [
            {
              required: false,
              message: 'Enter your Type!',
            },
          ],
          type: 'number',
          suffixDropdown: true, // Indicates suffix dropdown
          suffixOptions: [
            { label: 'Cost', value: 'cost' },
            { label: 'Hours', value: 'hours' },
          ],
          // suffixOptionsValue: 'Miss.',
          // defaultValue: 'Big',
          // value: 'R B'
        },
        
      ],
    },
  ];
  
  
  const CustomerForm = () => {
    const [current, setCurrent] = useState(0);
    const [formData,setFormData] = useState([]);
    const [isSubmitted, setIsSubmitted] = useState(false);
    const [selectedTags, setSelectedTags] = useState({});
    const [customers, setCustomers] = useState([]);
  
  
    const handleSelectChange = (value, field) => {
      
      const updatedFormData = { ...formData, [field.name]: value };
      
      // Handle dependencies
      formFields.forEach(section => {
        section.fields.forEach(f => {
          if (f.depends && f.depends === field.name) {
            updatedFormData[f.name] = null; // Reset dependent field value
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
    
    /*const fetchCustomers = async () => {
      try {
        const customersData = await getCustomers();
        setCustomers(customersData);
      } catch (error) {
        console.error('Error fetching customers:', error);
        message.error('Failed to fetch customers. Please check the API endpoint.');
      }
    };*/
  
    const storeCustomerData = async (data) => {
      try {
        const response = await axios.post('/customers', data);
        return response.data;
      } catch (error) {
        console.error('Error storing customer data:', error);
        throw error; // Re-throw the error so it can be caught and handled in the form
      }
    };
    
    const onFinish = async (values) => {
      const formDataValues = { ...formData, ...values };
      try {
        const response = await storeCustomerData(formDataValues);
        console.log('Form data stored successfully:', response);
        const customers = await getCustomers();
        console.log('Fetched customers:', customers);
        message.success('Customer Details Added Successfully');
        form.resetFields();
      } catch (error) {
        console.error('Error handling form submission:', error);
        message.error('Failed to handle form submission. Please try again later.');
      }
    };
    const steps = formFields.map((section, sectionIndex) => (
      <div key={section.title} id={section.title.toLowerCase().replace(/\s+/g, '_')} >
        <h3>{section.title}</h3>
        <div style={section.fields.length === 1 ? fullgridStyle : gridStyle} className={section.title.toLowerCase().replace(/\s+/g, '_')}>
          {section.fields.map((field, fieldIndex) => {
            let rules = field.rules; // Initially set to the provided rules
  
            // If defaultValue or value exists, skip applying rules
            if (field.defaultValue || field.value) {
              field.rules = undefined;
            }
            return (
              <div key={fieldIndex} style={{width:'100%'}} className={section.title.toLowerCase().replace(/\s+/g, '_')+'_box'}>
                <Form.Item
                  name={field.name}
                  label={field.label}
                  rules={field.rules}
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
                  ) : field.type === 'avatar' ? (
                    <Upload
                      listType="picture"
                      maxCount={1}
                      // beforeUpload={beforeUpload}
                      // onChange={handleChange}
                    >
                      <img
                        src={field.imgUrl || dummyImg }
                        alt="avatar"
                        style={{
                          width:200,height:200,borderRadius:10,
                        }}
                      />
                      <Button type='button' icon={<UploadOutlined />}>Click to Upload</Button>
                    </Upload>
                  ) : field.type === 'location' ? (
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
                  ) : field.type === 'tagfield' ? (
                    <Select
                      mode="multiple"
                      style={{ width: '100%' }}
                      placeholder="Select tags"
                      defaultValue={field.value}
                      options={field.tagOptions.map((option, index) => ({
                        label: (
                          <Space key={index}>
                            <span aria-label={option.label}
                              className={`badge-${option.value}`}
                              style={{ display: 'block', width: '14px', height: '14px', borderRadius: '50%' }}>
                            </span>
                            {option.label}
                          </Space>
                        ),
                        value: option.value,
                      }))}
                    />
                  ) : field.type === 'tagfields' ? (
                    <Select
                      mode="multiple"
                      style={{ width: '100%' }}
                      placeholder="Select Projects"
                      defaultValue={field.value}
                      options={field.projectOptions.map((option, index) => ({
                        label: (
                          <Space key={index}>
                            <span aria-label={option.label}
                              className={`badge-${option.value}`}
                              style={{ display: 'block', width: '14px', height: '14px', borderRadius: '50%' }}>
                            </span>
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
            );
          })}
        </div>
      </div>
    ));
    const next = async () => {
      try {
        // Validate all fields
        const values = await form.validateFields();
        formFields[current].fields.forEach(field => {
          if (field.prefixDropdown) {
            values[field.name + '_prefix'] = formData[field.name + '_prefix'];
          }
          if (field.suffixDropdown) {
            values[field.name + '_suffix'] = formData[field.name + '_suffix'];
          }
        });
        // Check if any mandatory fields are empty
        const mandatoryFields = formFields[current].fields.filter(field => field.rules && field.rules.some(rule => rule.required));
        
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
          setFormData({ ...formData, ...values });
          console.log("Submitting form data:", values);
          setCurrent(current + 1);
        }
      } catch (err) {
        console.log("Validation error:", err);
      }
    };
    
  
    const prev = () => {
      setCurrent(current - 1);
    };
  
    const items = steps.map((item) => ({ key: item.title, title: item.title }));
  
    const [form] = Form.useForm();
  
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
  
    
  const submitForm = async (values) => {
    if (isSubmitted) return; // Prevent double submission
    setIsSubmitted(true);
  
    console.log("Submitting form data:", values);
  };
    return (
      <Form
        {...formItemLayout}
        form={form}
        name="customerform"
        onFinish={onFinish}
        scrollToFirstError
      >
        <Steps current={current} items={steps} size="small" title="first" />
        <div>{steps[current]}</div>
        <div style={{ marginTop: 24 }}>
          {current < steps.length - 1 && (
            <Button type="primary" onClick={next}>
              Next
            </Button>
          )}
          {current === steps.length - 1 && (
            <Button type="primary" htmlType="submit">
              Create
            </Button>
          )}
          {current > 0 && (
            <Button style={{ margin: '0 8px' }} onClick={prev}>
              Previous
            </Button>
          )}
        </div>
      </Form>
    );
  };
  
  export default CustomerForm;