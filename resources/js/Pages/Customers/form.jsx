import {
  AutoComplete,
  Button,
  Cascader,
  Checkbox,
  Col,
  Form,
  Input,
  Row,
  Select,
  DatePicker,
  Steps,
} from 'antd';
import React, { useState } from 'react';
import axios from 'axios';

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

const tailFormItemLayout = {
  wrapperCol: {
    xs: { span: 24, offset: 0 },
    sm: { span: 16, offset: 8 },
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
    title: "Contact Information",
    fields: [
      {
        name: 'contactowner',
        label: 'Contact Owner',
        rules: [{ required: true, message: 'Please select an Owner!' }],
        type: 'dropdown',
        options: [
          { label: 'Zohodemo', value: 'zohodemo' },
          { label: 'Quickbooksdemo', value: 'quickbooksdemo' },
          { label: 'Mailchimpdemo', value: 'mailchimpdemo' },
        ],
        defaultValue: 'zohodemo',
      },
      {
        name: 'leadsource',
        label: 'Lead Source',
        rules: [{ required: true, message: 'Please select a Lead!' }],
        type: 'dropdown',
        options: [
          { label: 'None', value: 'none' },
          { label: 'Lead 1', value: 'lead1' },
          { label: 'Lead 2', value: 'lead2' },
          { label: 'Lead 3', value: 'lead3' },
        ],
        defaultValue: 'none',
      },
      {
        name: 'firstname',
        label: 'First Name',
        rules: [
          { required: true, message: 'Enter your First Name!' },
        ],
        type: 'text',
        prefixDropdown: true,
        prefixOptions: [
          { label: 'Mr.', value: 'Mr.' },
          { label: 'Mrs.', value: 'Mrs.' },
          { label: 'Miss.', value: 'Miss.' },
        ],
        prefixOptionsValue: 'Miss.',
        defaultValue: '',
      },
      {
        name: 'lastname',
        label: 'Last Name',
        rules: [{ required: true, message: 'Enter your Last Name!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'account_name',
        label: 'Account Name',
        rules: [{ required: true, message: 'Enter your Account Name!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'vendor_name',
        label: 'Vendor Name',
        rules: [{ required: true, message: 'Enter your Vendor Name!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'date_of_birth',
        label: 'Date of Birth',
        rules: [{ required: true, message: 'Please select your Date of Birth!' }],
        type: 'datepicker',
      },
      {
        name: 'email_opt_out',
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
        name: 'mailing_street',
        label: 'Mailing Street',
        rules: [{ required: true, message: 'Enter your Mailing Street!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'other_street',
        label: 'Other Street',
        rules: [{ required: true, message: 'Enter your Other Street!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'mailing_city',
        label: 'Mailing City',
        rules: [{ required: true, message: 'Enter your Mailing City!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'other_city',
        label: 'Other City',
        rules: [{ required: true, message: 'Enter your Other City!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'mailing_state',
        label: 'Mailing State',
        rules: [{ required: true, message: 'Enter your Mailing State!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'other_state',
        label: 'Other State',
        rules: [{ required: true, message: 'Enter your Other State!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'mailing_country',
        label: 'Mailing Country',
        rules: [{ required: true, message: 'Enter your Mailing Country!' }],
        type: 'text',
        defaultValue: '',
      },
      {
        name: 'other_country',
        label: 'Other Country',
        rules: [{ required: true, message: 'Enter your Other Country!' }],
        type: 'text',
        defaultValue: '',
      },
    ],
  },
  {
    title: "Description Information",
    fields: [
      {
        name: "description",
        label: "Description",
        type: "textarea",
        defaultValue: '',
      },
    ],
  },
];

const steps = formFields.map((section, sectionIndex) => (
  <div key={sectionIndex}>
    <h3>{section.title}</h3>
    <div style={gridStyle}>
      {section.fields.map((field, fieldIndex) => (
        <div key={fieldIndex} style={gridItemStyle}>
          <Form.Item
            name={field.name}
            label={field.label}
            rules={field.rules}
            valuePropName={field.type === 'checkbox' ? 'checked' : 'value'}
          >
            {field.type === 'dropdown' ? (
              <Select>
                {field.options.map((option, optionIndex) => (
                  <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                ))}
              </Select>
            ) : field.type === 'datepicker' ? (
              <DatePicker style={{ width: '100%' }} />
            ) : field.type === 'checkbox' ? (
              <Checkbox />
            ) : field.prefixDropdown ? (
              <Input
                addonBefore={
                  <Form.Item name={`${field.name}_prefix`} noStyle>
                    <Select>
                      {field.prefixOptions.map((option, optionIndex) => (
                        <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                }
                style={{ width: '100%' }}
              />
            ) : field.type === 'textarea' ? (
              <Input.TextArea />
            ) : (
              <Input />
            )}
          </Form.Item>
        </div>
      ))}
    </div>
  </div>
));

const CustomerForm = () => {
  const [current, setCurrent] = useState(0);
  const [form] = Form.useForm();
  const [formData, setFormData] = useState({});
  const [isSubmitted, setIsSubmitted] = useState(false);

  const next = async () => {
    try {
      const values = await form.validateFields();
      setFormData({ ...formData, ...values });

      if (current === steps.length - 1) {
        // Submit the form if it's the last step
        await submitForm({ ...formData, ...values });
      } else {
        setCurrent(current + 1);
      }
    } catch (err) {
      console.log("Validation error:", err);
    }
  };

  const prev = () => {
    setCurrent(current - 1);
  };

  const submitForm = async (values) => {
    if (isSubmitted) return; // Prevent double submission
    setIsSubmitted(true);

    console.log("Submitting form data:", values);

    try {
      const res = await axios.post("http://127.0.0.1:8000/api/project", values);
      console.log("Response:", res);
    } catch (err) {
      if (err.response) {
        console.log("Response data:", err.response.data);
        console.log("Response status:", err.response.status);
        console.log("Response headers:", err.response.headers);
      } else if (err.request) {
        console.log("Request data:", err.request);
      } else {
        console.log("Error message:", err.message);
      }
      console.log("Error config:", err.config);
    } finally {
      setIsSubmitted(false);
    }
  };

  const onFinish = async (values) => {
    // Submit the form on the final step
    await submitForm({ ...formData, ...values });
  };

  const items = formFields.map((section) => ({ key: section.title, title: section.title }));

  return (
    <Form
      {...formItemLayout}
      form={form}
      name="customerForm"
      onFinish={onFinish}
      initialValues={formFields.reduce((acc, section) => {
        section.fields.forEach(field => {
          acc[field.name] = field.defaultValue;
          if (field.prefixDropdown) {
            acc[`${field.name}_prefix`] = field.prefixOptionsValue;
          }
        });
        return acc;
      }, {})}
      scrollToFirstError
    >
      <Steps current={current} items={items} />

      {steps[current]}

      <Form.Item {...tailFormItemLayout}>
        {current > 0 && (
          <Button style={{ margin: '0 8px' }} onClick={prev}>
            Previous
          </Button>
        )}
        {current < steps.length - 1 && (
          <Button type="primary" onClick={next}>
            Next
          </Button>
        )}
        {current === steps.length - 1 && (
          <Button type="primary" htmlType="submit">
            Submit
          </Button>
        )}
      </Form.Item>
    </Form>
  );
};
  export default CustomerForm;
  