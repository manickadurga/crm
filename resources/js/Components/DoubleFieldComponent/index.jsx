import React from 'react';
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
    Steps, Image,
    Flex, Upload, message
  } from 'antd';

const DoubleFieldComponent = ({ fields }) => {
  return (
    <>
      {fields.map((subField, subFieldIndex) => (
        <div key={subFieldIndex} style={{ marginBottom: 10 }}>
          {subField.type === 'dropdown' ? (
            <Select style={{ width: '100%' }} defaultValue={subField.defaultValue}>
              {subField.options.map((option, optionIndex) => (
                <Select.Option key={optionIndex} value={option.value}>
                  {option.label}
                </Select.Option>
              ))}
            </Select>
          ) : subField.type === 'input' ? (
            <Input style={{ width: '100%' }} defaultValue={subField.defaultValue} />
          ) : subField.type === 'inputNumber' ? (
            <InputNumber style={{ width: '100%' }} defaultValue={subField.defaultValue} />
          ) : subField.type === 'tags' ? (
            <Select mode="tags" style={{ width: '100%' }} defaultValue={subField.defaultValue}>
              {subField.options.map((option, optionIndex) => (
                <Select.Option key={optionIndex} value={option.value}>
                  {option.label}
                </Select.Option>
              ))}
            </Select>
          ) : subField.type === 'textarea' ? (
            <Input.TextArea style={{ width: '100%' }} />
          ) : null}
        </div>
      ))}
    </>
  );
};

export default DoubleFieldComponent;
