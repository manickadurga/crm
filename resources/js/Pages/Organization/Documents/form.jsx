import React, { useState, useEffect } from "react";
import { Button, Form, Input, Tooltip, message, Upload } from "antd";
import { ArrowLeftOutlined, UploadOutlined } from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from "axios";

const formItemLayout = {
    labelCol: { xs: { span: 24 }, sm: { span: 8 } },
    wrapperCol: { xs: { span: 24 }, sm: { span: 16 } },
};

const gridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))",
    gap: "20px",
    marginBottom: "20px",
};

const gridItemStyle = {
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
};

const formFields = [
    {
        name: "document_name",
        label: "Name",
        type: "text",
        rules: [{ required: true, message: "Please enter the document name!" }],
    },
    {
        name: "url",
        label: "URL",
        type: "url",
        rules: [
            {
                required: true,
                message: "Please upload a file or enter a URL!",
            },
        ],

        component: (urlInput, handleUrlChange, handleUpload) => (
            <div style={{ display: "flex", alignItems: "center" }}>
                <Input
                    value={urlInput}
                    onChange={handleUrlChange}
                    placeholder="Enter URL or leave blank if uploading"
                    style={{ flex: 1, marginRight: "8px" }}
                />

                <Upload
                    name="file"
                    action="http://127.0.0.1:8000/api/documents"
                    showUploadList={false}
                    onChange={handleUpload}
                    accept=".jpg,.jpeg,.png"
                >
                    <Button
                        icon={<UploadOutlined />}
                        style={{
                            borderRadius: "50%",
                            width: "120px",
                            height: "40px",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                        }}
                    >
                        Upload
                    </Button>
                </Upload>
            </div>
        ),
    },
];

const DocumentsForm = () => {
    const { id } = useParams();
    const [form] = Form.useForm();
    const [document, setDocument] = useState(null);
    const [urlInput, setUrlInput] = useState("");

    useEffect(() => {
        if (id) {
            axios
                .get(`http://127.0.0.1:8000/api/documents/${id}`)
                .then((response) => {
                    setDocument(response.data);
                    form.setFieldsValue(response.data);
                    setUrlInput(response.data.url || "");
                })
                .catch((error) => {
                    console.error("Error fetching document data:", error);
                });
        }
    }, [id, form]);

    const storeDocumentData = async (values) => {
        try {
            const response = await axios.post(
                "http://127.0.0.1:8000/api/documents",
                values
            );
            message.success("Document stored successfully!");
            return response.data;
        } catch (error) {
            console.error("Error storing document data:", error);
            throw error;
        }
    };

    const updateDocumentData = async (id, values) => {
        try {
            const response = await axios.put(
                `http://127.0.0.1:8000/api/documents/${id}`,
                values
            );
            message.success("Document updated successfully!");
            return response.data;
        } catch (error) {
            console.error("Error updating document data:", error);
            throw error;
        }
    };

    const onFinish = async (values) => {
        try {
            if (id) {
                await updateDocumentData(id, values);
            } else {
                await storeDocumentData(values);
            }
        } catch (error) {
            console.error("Operation failed:", error);
        }
    };

    const handleUpload = ({ file }) => {
        if (file.status === "done") {
            const uploadedUrl = file.response.url; // Assuming response contains the file URL
            setUrlInput(uploadedUrl);
            form.setFieldsValue({ url: uploadedUrl });
            message.success(`${file.name} file uploaded successfully`);
        } else if (file.status === "error") {
            message.error(`${file.name} file upload failed`);
        }
    };

    const handleUrlChange = (e) => {
        setUrlInput(e.target.value);
        form.setFieldsValue({ url: e.target.value });
    };

    return (
        <>
            <Link to="/documents">
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>

            <Form
                {...formItemLayout}
                form={form}
                name="documentForm"
                onFinish={onFinish}
                scrollToFirstError
                style={{ marginTop: "24px" }}
            >
                <h3>{id ? "Update Document" : "Add Document"}</h3>
                <div style={gridStyle}>
                    {formFields.map((field, index) => (
                        <div key={index} style={gridItemStyle}>
                            <Form.Item
                                name={field.name}
                                label={field.label}
                                rules={field.rules}
                                className="form-item"
                            >
                                {field.component ? (
                                    field.component(
                                        urlInput,
                                        handleUrlChange,
                                        handleUpload
                                    )
                                ) : (
                                    <Input
                                        type={field.type}
                                        placeholder={`Enter ${field.label}`}
                                    />
                                )}
                            </Form.Item>
                        </div>
                    ))}
                </div>

                <div style={{ marginTop: 24, textAlign: "center" }}>
                    <Button type="primary" htmlType="submit">
                        {id ? "Update" : "Create"}
                    </Button>
                </div>
            </Form>
        </>
    );
};

export default DocumentsForm;
