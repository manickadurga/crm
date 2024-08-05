import React, { useState, useEffect } from 'react';
import { Button, Form, Input, Select, Checkbox, DatePicker, message } from 'antd';
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Link, useParams, useNavigate } from "react-router-dom";
import axios from 'axios';
import dayjs from 'dayjs';
import EditableTable from './editableTable';

const { Option } = Select;

const formItemLayout = {
  labelCol: {
    xs: { span: 24 },
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

const EstimatesForm = () => {
  const { id } = useParams();
  const [current, setCurrent] = useState(0);
  const [form] = Form.useForm();
  const navigate = useNavigate();
  const [estimateData, setEstimateData] = useState(null);
  const [estimateFormField, setEstimateFormField] = useState([]);
  const [selectedDueDate, setSelectedDueDate] = useState(null);
  const [selectedEstimateDate, setSelectedEstimateDate] = useState(null);

  useEffect(() => {
    // Fetch form fields
    axios.get('http://127.0.0.1:8000/form-fields?name=Estimates')
      .then((res) => {
        setEstimateFormField(Array.isArray(res.data) ? res.data : []);
      })
      .catch((error) => {
        console.error("Error fetching form fields:", error);
      });
  }, []);

  useEffect(() => {
    console.log("estimateFormFields state has been set:", estimateFormField);
  }, [estimateFormField]);

  useEffect(() => {
    // Fetch estimate data by ID
    const fetchEstimate = async () => {
      try {
        const response = await axios.get(`http://127.0.0.1:8000/estimates/${id}`);
        console.log('Estimate data fetched:', response.data);
        setEstimateData(response.data);
      } catch (error) {
        console.error('Error fetching estimate:', error);
        if (error.response && error.response.status === 404) {
          console.error('Estimate not found.');
        } else {
          console.error('An unexpected error occurred.');
        }
      }
    };
    if (id) {
      fetchEstimate();
    }
  }, [id]);

  useEffect(() => {
    if (estimateData) {
      // Format duedate and estimatedate to Day.js format
      const formattedData = {
        ...estimateData,
        duedate: estimateData.duedate ? dayjs(estimateData.duedate) : null,
        estimatedate: estimateData.estimatedate ? dayjs(estimateData.estimatedate) : null,
      };

      // Set form fields with estimateData values
      form.setFieldsValue(formattedData);
    }
  }, [estimateData, form]);

  const onFinish = async (values) => {
    console.log('Received values from form:', values);
    const url = id ? `http://127.0.0.1:8000/estimates/${id}` : 'http://127.0.0.1:8000/estimates';
    const method = id ? 'put' : 'post';

    try {
      const response = await axios({ method, url, data: values });
      console.log(`${id ? 'Estimate updated:' : 'Estimate created:'}`, response.data);
      message.success(`${id ? 'Estimate updated successfully!' : 'Estimate created successfully!'}`);
      navigate('/estimates'); // Navigate to estimates list or show success message
    } catch (error) {
      console.error(`There was an error ${id ? 'updating' : 'creating'} the estimate!`, error);
      // Handle error, show error message
    }
  };

  const steps = Array.isArray(estimateFormField) ? estimateFormField.map((section) => (
    <div key={section.title}>
      <h3>{section.title}</h3>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '16px' }}>
        {Array.isArray(section.fields) && section.fields.map((field, fieldIndex) => (
          <div key={fieldIndex} style={{ width: '100%' }}>
            <Form.Item
              name={field.name}
              label={field.label}
              rules={field.rules}
              className="form-item"
            >
              {field.type === '16' ? (
                <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
                  {Array.isArray(field.options) && field.options.map((option, optionIndex) => (
                    <Option key={optionIndex} value={option.value}>{option.label}</Option>
                  ))}
                </Select>
              ) : field.type === 'tagfields' ? (
                <Select
                  mode="multiple"
                  style={{ width: '100%' }}
                  placeholder="Select tags"
                  defaultValue={field.value}
                  options={field.tagOptions.map((option) => ({
                    label: (
                      <span>
                        <span aria-label={option.label} className={`badge-${option.value}`} style={{ display: 'block', width: '14px', height: '14px', borderRadius: '50px' }}></span>
                        {option.label}
                      </span>
                    ),
                    value: option.value,
                  }))}
                />
              ) : field.type === '4' ? (
                <Input type="number" min={0} />
              ) : field.type === '5' ? (
                <DatePicker
                  style={{ width: '100%' }}
                  value={field.name === 'duedate' ? selectedDueDate : selectedEstimateDate}
                  onChange={(date) => field.name === 'duedate' ? setSelectedDueDate(date) : setSelectedEstimateDate(date)}
                />
              ) : field.type === 'checkbox' ? (
                <Checkbox defaultChecked={field.value || field.defaultValue}></Checkbox>
              ) : field.prefixDropdown ? (
                <Input
                  addonBefore={<Select defaultValue={field.prefixOptionsValue || field.prefixOptions[0].value}>
                    {Array.isArray(field.prefixOptions) && field.prefixOptions.map((option, optionIndex) => (
                      <Option key={optionIndex} value={option.value}>{option.label}</Option>
                    ))}
                  </Select>}
                  style={{ width: '100%' }}
                  defaultValue={field.value || field.defaultValue}
                />
              ) : field.suffixDropdown ? (
                <Input
                  addonAfter={<Select defaultValue={field.suffixOptionsValue || field.suffixOptions[0].value}>
                    {Array.isArray(field.suffixOptions) && field.suffixOptions.map((option, optionIndex) => (
                      <Option key={optionIndex} value={option.value}>{option.label}</Option>
                    ))}
                  </Select>}
                  style={{ width: '100%' }}
                  defaultValue={field.value || field.defaultValue}
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
  )) : [];

  return (
    <>
      <Button type="link" onClick={() => navigate("/estimates")}>
        <ArrowLeftOutlined /> Back to Estimates
      </Button>
      <Form
        {...formItemLayout}
        form={form}
        name="estimatesform"
        onFinish={onFinish}
        scrollToFirstError
      >
        {steps[current]}
        <Form.Item {...tailFormItemLayout}>
          <div style={{ display: 'flex', flexWrap: 'wrap' }}>
            <EditableTable />
          </div>
          {current < steps.length - 1 && (
            <Button type="primary" onClick={() => setCurrent(current + 1)}>
              Next
            </Button>
          )}
          {current < steps.length - 0 && (
            <Link to="/estimates">
              <Button type="primary" htmlType="button" style={{ marginLeft: '10px', marginRight: '10px' }}>
                Cancel
              </Button>
            </Link>
          )}
          {current === steps.length - 1 && (
            <Button type="primary" htmlType="submit">
              {id ? 'Update' : 'Create'}
            </Button>
          )}
          {current > 0 && (
            <Button style={{ margin: '0 8px' }} onClick={() => setCurrent(current - 1)}>
              Previous
            </Button>
          )}
        </Form.Item>
      </Form>
    </>
  );
};

export default EstimatesForm;
