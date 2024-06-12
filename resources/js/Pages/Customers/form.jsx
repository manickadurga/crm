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
  import React, { useState } from 'react';
  import { SearchOutlined, UploadOutlined, LoadingOutlined, RightCircleOutlined, LeftCircleOutlined } from "@ant-design/icons";
  import dummyImg from "../../../../public/assets/img/noprofile.png"
  import DoubleFieldComponent from '../../Components/DoubleFieldComponent';
  // import GoogleMapComponent from '../../Components/GoogleMap';
  import LeafletMap from '../../Components/LeafletMap';

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
          name: 'avatar',
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
          type: 'text',
          prefixDropdown: true, // Indicates prefix dropdown
          prefixOptions: [
            { label: 'Mr.', value: 'Mr.' },
            { label: 'Mrs.', value: 'Mrs.' },
            { label: 'Miss.', value: 'Miss.' },
          ],
          prefixOptionsValue: 'Miss.',
          defaultValue: 'Big',
          value: 'R B'
        },
        {
          name: 'contactowner',
          label: 'Contact Owner',
          rules: [
            {
              required: true,
              message: 'Please select an Owner!',
            },
          ],
          type: 'dropdown',
          options: [
            { label: 'Zohodemo', value: 'zohodemo' },
            { label: 'Quickbooksdemo', value: 'quickbooksdemo' },
            { label: 'Mailchimpdemo', value: 'mailchimpdemo' },
          ],
          defaultValue: 'Zohodemo',
        },
        {
          name: 'leadsource',
          label: 'Lead Source',
          rules: [
            {
              required: true,
              message: 'Please select an Lead!',
            },
          ],
          type: 'dropdown',
          options: [
            { label: 'None', value: 'none' },
            { label: 'Lead 1', value: 'lead1' },
            { label: 'Lead 2', value: 'lead2' },
            { label: 'Lead 3', value: 'lead3' },
          ],
          defaultValue: 'none',
          value: 'Lead 1'
        },
        {
          name: 'lastname',
          label: 'Last Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
          ],
          value: 'Choudhry'
        },
        {
          name: 'accountname',
          label: 'Account Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Account Name!',
            },
          ],
        },
        {
          name: 'vendorname',
          label: 'Vendor Name',
          rules: [
            {
              type: 'text',
              message: 'The input is not valid!',
            },
            {
              required: true,
              message: 'Enter your Vendor Name!',
            },
          ],
        },
        {
          name: 'dob',
          label: 'Date of Birth',
          rules: [
            {
              required: true,
              message: 'Please select your Date of Birth!',
            },
          ],
          type: 'datepicker',
        },
        {
          name: 'emailoptout',
          label: 'Email Opt Out',
          type: 'checkbox',
          defaultValue: true,
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
              required: true,
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
        {
          name: "tagsfield",
          label: "Tags Field",
          type: "tagfield",
          tagOptions: [  
            {
              label: 'Urgentinsse',
              value: 'urgentinsse',
            },
            {
              label: 'Vimp ortant',
              value: 'vimportant',
            },
            {
              label: 'Pen Dingia',
              value: 'pendingia',
            },
            {
              label: 'C Ompletedee',
              value: 'completedee',
            },
            {
              label: 'Paid',
              value: 'paid',
            },
          ],
          value: ['important', 'pending']
        },
        {
          name: "tagsfields",
          label: "Tags Fields",
          type: "tagfields",
          tagOptions: [  
            {
              label: 'Urgentinsse',
              value: 'urgentinsse',
              imgUrl: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png'
            },
            {
              label: 'Vimp ortant',
              value: 'vimportant',
              imgUrl: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png'
            },
            {
              label: 'Pen Dingia',
              value: 'pendingia',
              imgUrl: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png'
            },
            {
              label: 'C Ompletedee',
              value: 'completedee',
              imgUrl: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png'
            },
            {
              label: 'Paidy',
              value: 'paidy',
              imgUrl: 'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png'
            },
          ],
          // value: ['important', 'pending']
        },
      ]
    },
  ];

  
  const CustomerForm = () => {
    const [current, setCurrent] = useState(0);
    const [formData,setFormData] = useState([]);
    const [isSubmitted, setIsSubmitted] = useState(false);
    const [selectedTags, setSelectedTags] = useState({});

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
    console.log("vvvv", value,fieldName);
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
    
    

    const steps = formFields.map((section, sectionIndex) => (
      {
        title: section.title, 
        // description: 'This is a description.',
        content: (
          <div key={section.title} id={section.title.toLowerCase().replace(/\s+/g, '_')} >
            <h3>{section.title} &nbsp; :</h3>
            <div style={section.fields.length === 1 ? fullgridStyle : gridStyle} className={section.title.toLowerCase().replace(/\s+/g, '_')}>
              {section.fields.map((field, fieldIndex) => {
                
                let rules = field.rules;
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
                      ) : field.type === 'tagfields' ? (
                      <div>
                        <Select
                          mode="multiple"
                          style={{ width: '100%' }}
                          placeholder="Select tags"
                          defaultValue={selectedTags[field.name] || field.value} // Set defaultValue to the array of selected values
                          onChange={(value) => handleTagChange(value, field.name)}
                          options={field.tagOptions.map((option, index) => ({
                            label: (
                              <Space>
                                <Image
                                  src={option.imgUrl}
                                  alt={option?.label}
                                  style={{ width: '24px', height: '24px', borderRadius: '50%', marginRight: '10px' }}
                                />
                                {option.label}
                              </Space>
                            ),
                            value: option.value,
                          }))}
                        />
                        <div style={{ marginTop:16, display:'flex',flexDirection:'column', }}>
                          {(selectedTags[field.name] || []).map((tag) => {
                            const option = field.tagOptions.find((opt) => opt.value === tag);
                            return (
                              <Tag
                                key={tag}
                                color={option?.color || 'default'} // Provide a default color if color is undefined
                                style={{ marginBottom: '5px', background:'transparent',fontSize:13, }}
                              >
                                <Image
                                  src={option.imgUrl}
                                  alt={option?.label} // Provide alt text for accessibility
                                  style={{ width: '28px', height: '28px', borderRadius: '50%', marginRight: '10px' }}
                                />
                                {option?.label}
                              </Tag>
                            );
                          })}
                        </div>
                      </div>
                    // ) : field.type === 'tagfields' ? (
                    //   <Select
                    //     mode="multiple"
                    //     style={{ width: '100%' }}
                    //     placeholder="Select tags"
                    //     defaultValue={field.value}
                    //     className='listedView'
                    //     options={field.tagOptions.map((option, index) => ({
                    //       label: (
                    //         <Space>
                    //           <span aria-label={option.label}  
                    //             className={`badge-${option.value}`} 
                    //             style={{display:'block', width:'14px', height:'14px', borderRadius:'50px'}}>
                    //           </span>
                    //           {option.label}
                    //         </Space>
                    //       ),
                    //       value: option.value,
                    //     }))}
                    //   />
                    ) : field.type === 'tagfield' ? (
                        <Select
                          mode="multiple"
                          style={{ width: '100%' }}
                          placeholder="Select tags"
                          defaultValue={field.value}
                          options={field.tagOptions.map((option, index) => ({
                            label: (
                              <Space>
                                <span aria-label={option.label}  
                                  className={`badge-${option.value}`} 
                                  style={{display:'block', width:'14px', height:'14px', borderRadius:'50px'}}>
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
                        <Input type={field.type} style={{ width: '100%' }} defaultValue={field.value || field.defaultValue} />
                      )}
                    </Form.Item>
                  </div>
                );
              })}
            </div>
          </div>
       ),
      }
    ));

    const next = async () => {
      try {
        // Validate all fields
        const values = await form.validateFields();
        // formFields[current].fields.forEach(field => {
        //   if (field.type === 'doublefield') {
        //     field.fields.forEach(subField => {
        //       if (subField.name) {
        //         // Get value from values object
        //         values[subField.name] = values[subField.name] || [];
        //         if (subField.type === 'tags') {
        //           // For tags, convert value to array if not already
        //           if (!Array.isArray(values[subField.name])) {
        //             values[subField.name] = [values[subField.name]];
        //           }
        //         }
        //         // Push subField value into the array
        //         values[subField.name].push(subField.defaultValue);
        //       }
        //     });
        //   }
        // });
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
    // try {
    //   const res = await axios.post("http://127.0.0.1:8000/api/project", values);
    //   console.log("Response:", res);
    // } catch (err) {
    //   if (err.response) {
    //     console.log("Response data:", err.response.data);
    //     console.log("Response status:", err.response.status);
    //     console.log("Response headers:", err.response.headers);
    //   } else if (err.request) {
    //     console.log("Request data:", err.request);
    //   } else {
    //     console.log("Error message:", err.message);
    //   }
    //   console.log("Error config:", err.config);
    // } finally {
    //   setIsSubmitted(false);
    // }
  };

    const onFinish = async (values) => {
      // console.log('Received values from form:', values);
      await submitForm({ ...formData, ...values });

    };
    return (
      <Form
        {...formItemLayout}
        form={form}
        name="customerform"
        onFinish={onFinish}
        scrollToFirstError
      >
        <Steps current={current} items={steps} size="small" title="oneee" />
        <div>{steps[current].content}</div>
        <div style={{ marginTop: 24, display:'flex', justifyContent:'space-between' }}>
          {/* {current > 0 && ( */}
          
            <Button style={{ margin: '0 8px' }} onClick={prev}  
              icon={<LeftCircleOutlined />}
              disabled={current === 0}
            >
              Previous
            </Button>
          
          {current < steps.length - 1 && (
            <Button type="primary" onClick={next} icon={<RightCircleOutlined />} iconPosition='right'>
              Next &nbsp;
            </Button>
          )}
          {current === steps.length - 1 && (
            <Button type="primary" htmlType="submit">
              Create
            </Button>
          )}
        </div>
      </Form>
    );
  };

  export default CustomerForm;