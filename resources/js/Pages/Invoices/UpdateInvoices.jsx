import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Form, Input, Button, message, Select, DatePicker, Checkbox } from "antd";
import moment from 'moment';
import { getInvoicesId, updateInvoices } from "../../API";


const { Option } = Select;

const contacts = [
  { label: 'Zohodemo', value: 'zohodemo' },
  { label: 'Quickbooksdemo', value: 'quickbooksdemo' },
  { label: 'Mailchimpdemo', value: 'mailchimpdemo' },
];

const currency = ['USD', 'EUR', 'GBP']; // Add other currencies as needed

const tagOptions = [
  { label: 'Urgent', value: 'urgent' },
  { label: 'Important', value: 'important' },
  { label: 'Pending', value: 'pending' },
  { label: 'Completed', value: 'completed' },
  { label: 'Paid', value: 'paid' },
];

const UpdateInvoice = ({ onClose, contacts = [], currency = [] }) => {
  const { id } = useParams();
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);
  const [invoice, setInvoice] = useState({});

  useEffect(() => {
    const fetchInvoice = async () => {
      try {
        setLoading(true);
        const invoice = await getInvoicesId(id);
        invoice.invoicedate = invoice.invoicedate ? moment(invoice.invoicedate) : null;
        invoice.duedate = invoice.duedate ? moment(invoice.duedate) : null;
        form.setFieldsValue(invoice);
        setLoading(false);
      } catch (error) {
        console.error("Error fetching invoice:", error);
        message.error("Failed to load invoice data");
        setLoading(false);
      }
    };

    if (id) {
      fetchInvoice();
    }
  }, [id, form]);

  const handleInput = (e) => {
    e.persist();
    setInvoice({...invoice, [e.target.name]: e.target.value});
  }

  const update = (e) =>{
    e.preventDefault();

    setLoading(true);
    const data = {
      invoicenumber: invoice.invoicenumber,
      contacts: invoice.contacts,
      invoicedate: invoice.invoicedate,
      duedate: invoice.duedate,
      discount: invoice.discount,
      currency: invoice.currency,
      terms: invoice.terms,
      tags: invoice.tags,
      tax1: invoice.tax1,
      tax2: invoice.tax2,
      applydiscount: invoice.applydiscount

    }
  }

  const onFinish = async (values) => {
    try {
      setLoading(true);
      await updateInvoices(id, values);
      message.success("Invoice updated successfully");
      setLoading(false);
      onClose();
    } catch (error) {
      console.error("Error updating invoice:", error);
      message.error("Failed to update invoice");
      setLoading(false);
    }
  };

  return (
    <Form form={form} layout="vertical" onFinish={onFinish} onSubmit={update} initialValues={{ invoicedate: null,
      duedate: null,}}>
      <Form.Item
        name="invoicenumber"
        label="Invoice Number"
        onChange={handleInput}
        rules={[{ required: true, message: 'Enter your Invoice Number!' }]}
      >
        <Input type="number" />
      </Form.Item>

      <Form.Item
        name="contacts"
        label="Contacts"
        rules={[{ required: true, message: 'Please select an Owner!' }]}
      >
        <Select placeholder="Select a contact" defaultValue="All Contacts">
          {contacts.map((contact) => (
            <Option key={contact.value} value={contact.value}>
              {contact.label}
            </Option>
          ))}
        </Select>
      </Form.Item>

      <Form.Item
        name="invoicedate"
        label="Invoice Date"
        rules={[{ required: true, message: 'Please select your INV Date!' }]}
      >
        <DatePicker format="YYYY-MM-DD" />
      </Form.Item>

      <Form.Item
        name="duedate"
        label="Due Date"
        rules={[{ required: true, message: 'Please select your Due Date!' }]}
      >
        <DatePicker format="YYYY-MM-DD"/>
      </Form.Item>

      <Form.Item
        name="discount"
        label="Discount"
        rules={[
          { required: true, message: 'Enter your Discount!' },
          { type: 'number', message: 'The input is not valid!' },
        ]}
      >
        <Input
          addonAfter={
            <Select defaultValue="%">
              <Option value="%">%</Option>
              <Option value="Flat">Flat</Option>
            </Select>
          }
        />
      </Form.Item>

      <Form.Item
        name="currency"
        label="Currency"
        rules={[{ required: true, message: 'Please select a currency!' }]}
      >
        <Select placeholder="Select a currency">
          {currency.map((curr) => (
            <Option key={curr} value={curr}>
              {curr}
            </Option>
          ))}
        </Select>
      </Form.Item>

      <Form.Item
        name="terms"
        label="Terms"
        // rules={[{  message: 'Please input the terms!' }]}
      >
        <Input.TextArea />
      </Form.Item>

      <Form.Item
        name="tags"
        label="Tags"
        // rules={[{  message: 'Please input the tags!' }]}
      >
        <Select mode="multiple" defaultValue={['important', 'pending']}>
          {tagOptions.map((tag) => (
            <Option key={tag.value} value={tag.value}>
              {tag.label}
            </Option>
          ))}
        </Select>
      </Form.Item>

      <Form.Item
        name="tax1"
        label="Tax 1"
        // rules={[{  message: 'Please input the tax 1!' }]}
      >
        <Input
          addonAfter={
            <Select defaultValue="%">
              <Option value="%">%</Option>
              <Option value="Flat">Flat</Option>
            </Select>
          }
        />
      </Form.Item>

      <Form.Item
        name="tax2"
        label="Tax 2"
        // rules={[{  message: 'Please input the tax 2!' }]}
      >
        <Input
          addonAfter={
            <Select defaultValue="%">
              <Option value="%">%</Option>
              <Option value="Flat">Flat</Option>
            </Select>
          }
        />
      </Form.Item>

      <Form.Item
        name="applydiscount"
        label="Apply Discount"
        valuePropName="checked"
        // rules={[{ message: 'Please input the apply discount!' }]}
      >
        <Checkbox defaultChecked />
      </Form.Item>
     
      <Form.Item>
        <Button type="primary" htmlType="submit" loading={loading}>
          Update Invoice
        </Button>
        <Button onClick={onClose} style={{ marginLeft: 8 }}>
          Cancel
        </Button>
      </Form.Item>
    </Form>
  );
};

export default UpdateInvoice;
