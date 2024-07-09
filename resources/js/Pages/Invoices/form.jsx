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
    Select,
    Alert,
    DatePicker,
    Dropdown,
    Steps,
    Space,
    Tooltip,
    message,
    Table
  
  } from 'antd';
  import React, { useState } from 'react';
  // import { Formik, Field, ErrorMessage } from 'formik';
  import { SearchOutlined, ArrowLeftOutlined, PlusOutlined } from "@ant-design/icons";

import { Link } from "react-router-dom";
import EditableTable from './editableTable';
import axios from 'axios';
// import countryCurrencyMap  from 'country-currency-map';

 // Add this line to check the output

 

//  console.log('countryCurrencyMap:', countryCurrencyMap);

// // Initialize currencyOptions based on countryCurrencyMap
// let currencyOptions = [];
// if (countryCurrencyMap) {
//   currencyOptions = Object.keys(countryCurrencyMap).map((countryCode) => ({
//     value: countryCurrencyMap[countryCode],
//     label: `${countryCode} - ${countryCurrencyMap[countryCode]}`,
//   }));
// }

// console.log('currencyOptions:', currencyOptions); // Log currencyOptions for debugging

  const { Option } = Select;
  const residences = [
    {
      value: 'zhejiang',
      label: 'Zhejiang',
      children: [
        {
          value: 'hangzhou',
          label: 'Hangzhou',
          children: [
            {
              value: 'xihu',
              label: 'West Lake',
            },
          ],
        },
      ],
    },
    {
      value: 'jiangsu',
      label: 'Jiangsu',
      children: [
        {
          value: 'nanjing',
          label: 'Nanjing',
          children: [
            {
              value: 'zhonghuamen',
              label: 'Zhong Hua Men',
            },
          ],
        },
      ],
    },
  ];
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
  const tailFormItemLayout = {
    wrapperCol: {
      xs: {
        span: 24,
        offset: 0,
      },
      sm: {
        span: 16,
        offset: 8,
      },
    },
  };
 
  const gridStyle = {
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
    gap: '16px',
  };

  const gridItemStyle = {
    width: '100%',
  };

    const formFields = [
      {
        title: "Add Invoice",
        fields: [
          {
            name: 'invoicenumber',
            label: 'Invoice Number',
            type: 'number',
            rules: [
              {
                required: true,
                message: 'Enter your Invoice Number!',
              },
            ],
          },
          {
            name: 'contacts',
            label: 'Contacts',
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
            defaultValue: 'All Contacts',
          },
          {
            name: 'invoicedate',
            label: 'Invoice Date',
            rules: [
              {
                required: true,
                message: 'Please select your INV Date!',
              },
            ],
            type: 'datepicker',
          },
          {
            name: 'duedate',
            label: 'Due Date',
            rules: [
              {
                required: true,
                message: 'Please select your Due Date!',
              },
            ],
            type: 'datepicker',
          },
          {
            name: 'discount',
            label: 'Discount',
            rules: [
              {
                type: 'text',
                message: 'The input is not valid!',
              },
              {
                required: true,
                message: 'Enter your Discount!',
              },
            ],
            type: 'number',
            suffixDropdown: true,
            suffixOptions: [
              { label: '%', value: '%' },
              { label: 'flat', value: 'Flat' },
            ],
            prefixOptionsValue: '%',
            defaultValue: '20',
          },
          {
            name: 'currency',
            label: 'Currency',
             
           
            rules:  [{required: true}]
            
          },
          {
            name: "terms",
            label: "Terms",
            type: "textarea"
          },
          {
            name: "tags",
            label: "Tags",
            type: "tagfields",
            tagOptions: [  
              {
                label: 'Urgent',
                value: 'urgent',
              },
              {
                label: 'Important',
                value: 'important',
              },
              {
                label: 'Pending',
                value: 'pending',
              },
              {
                label: 'Completed',
                value: 'completed',
              },
              {
                label: 'Paid',
                value: 'paid',
              },
            ],
            value: ['important', 'pending']
          },
          {
            name: 'tax1',
            label: 'Tax 1',
            type: 'number',
            suffixDropdown: true,
            suffixOptions: [
              { label: '%', value: '%' },
              { label: 'flat', value: 'Flat' },
            ],
            prefixOptionsValue: '%',
            defaultValue: '20',
          },
          {
            name: 'tax2',
            label: 'Tax 2',
            type: 'number',
            suffixDropdown: true,
            suffixOptions: [
              { label: '%', value: '%' },
              { label: 'flat', value: 'Flat' },
            ],
            prefixOptionsValue: '%',
            defaultValue: '20',
          },
          {
            name: 'applydiscount',
            label: 'Apply discount',
            type: 'checkbox',
            defaultValue: true,
          },
        ],
      },
    ];

 
  
  const steps = formFields.map((section, sectionIndex) => (
    <div key={section.title}>
      <h3>{section.title}</h3>
      <div style={gridStyle}>
        {section.fields.map((field, fieldIndex) => (
          <div key={fieldIndex} style={gridItemStyle}>
            <Form.Item
              name={field.name}
              label={field.label}
              rules={field.rules}
              className="form-item"
              // {...(field.type !== 'checkbox' ? {label: field.label } : {})}
            >
              {field.type === 'dropdown' ? (
                <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
                  {field.options.map((option, optionIndex) => (
                    <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                  ))}
                </Select>
              ) : field.type === 'tagfields' ? (
                <Select
                  mode="multiple"
                  style={{ width: '100%' }}
                  placeholder="Select tags"
                  defaultValue={field.value} // Set defaultValue to the array of selected values
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
              ) : field.type === 'datepicker' ? (
                <DatePicker style={{ width: '100%' }} />
              ) : field.type === 'checkbox' ? (
                <Checkbox defaultChecked={field.value || field.defaultValue}>
                  {/* {field.label} */}
                </Checkbox>
              ) : field.prefixDropdown ? (
                <Input
                  addonBefore={
                    <Select defaultValue={field.prefixOptionsValue || field.prefixOptions[0].value}>
                      {field.prefixOptions.map((option, optionIndex) => (
                        <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                      ))}
                    </Select>
                  }
                  style={{ width: '100%' }}
                  defaultValue={field.value || field.defaultValue}
                />
              ) : field.suffixDropdown ? (
                <Input
                  addonAfter={
                    <Select defaultValue={field.suffixOptionsValue || field.suffixOptions[0].value}>
                      {field.suffixOptions.map((option, optionIndex) => (
                        <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                      ))}
                    </Select>
                  }
                  style={{ width: '100%' }}
                  defaultValue={field.value || field.defaultValue}
                />
              ) : field.type === 'textarea' ? (
                <Input.TextArea />
              ) : (
                <Input
                  type={field.type}
                  style={{ width: '100%' }}
                  initialvalue={field.defaultValue}
                />
              )}
            </Form.Item>
          </div>
        ))}
      </div>
    </div>
  ));

  const InvoicesForm = () => {
    const [current, setCurrent] = useState(0);
    
    const next = () => {
      setCurrent(current + 1);
    };

    const prev = () => {
      setCurrent(current - 1);
    };
    const items = steps.map((item) => ({ key: item.title, title: item.title }));

    const [form] = Form.useForm();
    

    // const prefixSelector = (
    //   <Form.Item name="prefix" noStyle>
    //     <Select
    //       style={{
    //         width: 70,
    //       }}
    //     >
    //       <Option value="86">+86</Option>
    //       <Option value="87">+87</Option>
    //       <Option value="91">+91</Option>
    //     </Select>
    //   </Form.Item>
    // );
    // const suffixSelector = (
    //   <Form.Item name="suffix" noStyle>
    //     <Select
    //       style={{
    //         width: 70,
    //       }}
    //     >
    //       <Option value="USD">$</Option>
    //       <Option value="CNY">¥</Option>
    //     </Select>
    //   </Form.Item>
    // );
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
    const onFinish = async (values) => {
      console.log('Received values from form:', values);
      try {
        const res = await axios.post("http://127.0.0.1:8000/invoice", values);
        message.success("Invoices Added Successfully");
        console.log("Response:", res.data); // Logging the response data
        // Perform further actions here, if needed
      } catch (error) {
  console.error("Error occurred:", error);
  console.log("Response data:", error.response.data); // Log the response data for detailed error information
  console.log("Response status:", error.response.status); 
    };
  }
    
    return (
      <div>
        <Link to="/">
          <Tooltip title="Back" placement="right">
            <Button shape="circle" htmlType="button">
              <ArrowLeftOutlined />
            </Button>
          </Tooltip>
        </Link>
    
        <Form
          {...formItemLayout}
          name="invoicesform"
          onFinish={onFinish}
          scrollToFirstError
        >
          <div>{steps[current]}</div>
    
          <div style={{ display: 'flex', flexWrap: 'wrap' }}>
            <EditableTable />
          </div>
    
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
      </div>
    );
    
  };
  export default InvoicesForm;
  