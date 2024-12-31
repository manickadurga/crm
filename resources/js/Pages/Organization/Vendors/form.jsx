import {
    Button,
    Form,
    Input,
    Select,
    DatePicker,
    Checkbox,
    Tooltip,
} from "antd";
import React, { useState, useEffect } from "react";
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from "axios";

const { Option } = Select;

const formItemLayout = {
    labelCol: { xs: { span: 24 }, sm: { span: 8 } },
    wrapperCol: { xs: { span: 24 }, sm: { span: 16 } },
};

const VendorsForm = () => {
    const { id } = useParams(); // Get vendor ID from the URL
    const [form] = Form.useForm();
    const [current, setCurrent] = useState(0);

    // Fetch existing vendor data if updating
    useEffect(() => {
        if (id) {
            axios
                .get(`http://127.0.0.1:8000/api/vendors/${id}`)
                .then((response) => {
                    form.setFieldsValue(response.data);
                })
                .catch((error) => {
                    console.error("Error fetching vendor data:", error);
                });
        }
    }, [id, form]);

    const next = () => setCurrent(current + 1);
    const prev = () => setCurrent(current - 1);

    const storeVendorData = async (values) => {
        try {
            console.log("Sending data to store:", values);
            const response = await axios.post(
                "http://127.0.0.1:8000/api/vendors",
                values
            );
            console.log("Vendor stored successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error storing vendor data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    const updateVendorData = async (id, values) => {
        try {
            console.log("Sending data to update:", values);
            const response = await axios.put(
                `http://127.0.0.1:8000/api/vendors/${id}`,
                values
            );
            console.log("Vendor updated successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error updating vendor data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    const onFinish = async (values) => {
        try {
            if (id) {
                await updateVendorData(id, values);
            } else {
                await storeVendorData(values);
            }
            console.log("Operation successful!");
        } catch (error) {
            console.error("Operation failed:", error);
        }
    };

    return (
        <>
            <Link to="/vendors">
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>
            <Form
                {...formItemLayout}
                form={form}
                name="vendorsform"
                onFinish={onFinish}
                scrollToFirstError
            >
                {/* Vendor Details Form */}
                <Form.Item
                    name="vendor_name"
                    label="vendor_name"
                    rules={[
                        {
                            required: true,
                            message: "Please input the vendor's name!",
                        },
                    ]}
                >
                    <Input />
                </Form.Item>

                <Form.Item
                    name="phone"
                    label="phone"
                    rules={[
                        {
                            required: true,
                            message: "Please input the vendor's phone number!",
                        },
                    ]}
                >
                    <Input />
                </Form.Item>

                <Form.Item
                    name="email"
                    label="email"
                    rules={[
                        {
                            required: true,
                            message: "Please input the vendor's email!",
                        },
                        {
                            type: "email",
                            message: "The input is not a valid email!",
                        },
                    ]}
                >
                    <Input />
                </Form.Item>

                <Form.Item name="website" label="website">
                    <Input />
                </Form.Item>

                <Form.Item name="tags" label="tags">
                    <Select mode="multiple" placeholder="Select tags">
                        <Option value="VIP">VIP</Option>
                        <Option value="Urgent">Urgent</Option>
                        <Option value="Crazy">Crazy</Option>
                        <Option value="Broken">Broken</Option>
                        <Option value="Completed">Completed</Option>
                        <Option value="In process">In process</Option>
                    </Select>
                </Form.Item>

                <div style={{ marginTop: 24 }}>
                    <Button type="primary" htmlType="submit">
                        {id ? "Update" : "Create"}
                    </Button>
                    {current > 0 && (
                        <Button style={{ margin: "0 8px" }} onClick={prev}>
                            Previous
                        </Button>
                    )}
                </div>
            </Form>
        </>
    );
};

export default VendorsForm;
