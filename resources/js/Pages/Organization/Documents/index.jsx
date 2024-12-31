import React, { useState, useEffect } from "react";
import { Space, Button, Modal, message, Card, Row, Col } from "antd";
import {
    EyeOutlined,
    UnorderedListOutlined,
    WindowsOutlined,
    DeleteOutlined,
    EditOutlined,
    ArrowLeftOutlined,
    PlusOutlined,
} from "@ant-design/icons";
import { getDataFunction } from "../../../API"; // Adjust API import if needed
import { Link } from "react-router-dom";

const Documents = () => {
    const [dataSource, setDataSource] = useState([]);
    const [loading, setLoading] = useState(false);
    const [deleteModalVisible, setDeleteModalVisible] = useState(false);
    const [selectedDocument, setSelectedDocument] = useState(null);
    const [viewMode, setViewMode] = useState("card"); // Default view mode to card

    useEffect(() => {
        fetchDocuments();
    }, []);

    const fetchDocuments = async () => {
        setLoading(true);
        try {
            const res = await getDataFunction("documents", page);
            const documents = res.Data || []; // Adjust to the correct field if needed
            setDataSource(documents);
        } catch (error) {
            console.error("Error fetching documents data:", error);
        } finally {
            setLoading(false);
        }
    };

    const showDeleteModal = () => {
        setDeleteModalVisible(true);
    };

    const handleDelete = () => {
        // Call delete API here, then refresh the list
        message.success("Document deleted successfully!");
        fetchDocuments();
        setDeleteModalVisible(false);
        setSelectedDocument(null);
    };

    const onRowClick = (record) => {
        setSelectedDocument((prevSelected) =>
            prevSelected && prevSelected.id === record.id ? null : record
        );
    };

    const renderCards = () => (
        <Row gutter={16}>
            {dataSource.map((item, index) => (
                <Col key={item.id} span={6}>
                    <Card
                        title={item.title || item.name} // Adjust based on the field used for title
                        onClick={() => onRowClick(item)}
                        style={{
                            cursor: "pointer",
                            backgroundColor:
                                selectedDocument &&
                                selectedDocument.id === item.id
                                    ? "#f0f0f0"
                                    : "white",
                        }}
                    >
                        {/* Conditional rendering based on index or item properties */}
                        {index === 0 ? (
                            <p>
                                <a
                                    href={item.link1 || "#"}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {item.linkText1 || " Paid Days off Request"}
                                </a>
                            </p>
                        ) : index === 1 ? (
                            <p>
                                <a
                                    href={item.link2 || "#"}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {item.linkText2 ||
                                        " Unpaid Days off Request"}
                                </a>
                            </p>
                        ) : null}

                        {/* Render additional details if the document is selected */}
                        {selectedDocument &&
                            selectedDocument.id === item.id && (
                                <>
                                    <p>Role: {item.role || "Not Specified"}</p>
                                    {/* Add any additional details here */}
                                </>
                            )}

                        {/* Render key-value pairs for other fields */}
                        {Object.entries(item).map(([key, value]) => (
                            <p key={key}>
                                <strong>{key}:</strong>{" "}
                                {typeof value === "object"
                                    ? JSON.stringify(value)
                                    : value}
                            </p>
                        ))}
                    </Card>
                </Col>
            ))}
        </Row>
    );

    return (
        <Row>
            <Col span={24}>
                <Space size={20} direction="vertical" style={{ width: "100%" }}>
                    <div
                        style={{
                            position: "sticky",
                            display: "flex",
                            justifyContent: "space-between",
                            gap: "1rem",
                            top: 0,
                            background: "#fff",
                            zIndex: 1,
                        }}
                    >
                        <div style={{ display: "flex", alignItems: "center" }}>
                            <Link to="/documents">
                                <Button
                                    shape="circle"
                                    htmlType="button"
                                    size="small"
                                >
                                    <ArrowLeftOutlined />
                                </Button>
                            </Link>
                            <b style={{ fontSize: "18px", marginLeft: "18px" }}>
                                Documents
                            </b>
                        </div>

                        <div
                            style={{
                                display: "flex",
                                fontSize: "18px",
                                marginLeft: "18px",
                                gap: "5px",
                            }}
                        >
                            <Button
                                type="primary"
                                htmlType="button"
                                onClick={() => setInviteModalVisible(true)}
                            >
                                Invite
                            </Button>
                            {selectedDocument && (
                                <div style={{ gap: "2px" }}>
                                    <Link
                                        to={`/documents/view/${selectedDocument.id}`}
                                    >
                                        <Button
                                            type="link"
                                            style={{
                                                marginRight: "2px",
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        >
                                            <EyeOutlined />
                                        </Button>
                                    </Link>
                                    <Link
                                        to={`/documents/edit/${selectedDocument.id}`}
                                    >
                                        <Button
                                            type="link"
                                            style={{
                                                marginRight: "2px",
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        >
                                            <EditOutlined />
                                        </Button>
                                    </Link>
                                    <Button
                                        type="link"
                                        onClick={showDeleteModal}
                                        style={{
                                            marginRight: "2px",
                                            border: "1px solid #ccc",
                                            background: "white",
                                        }}
                                    >
                                        <DeleteOutlined />
                                    </Button>
                                </div>
                            )}

                            <Link to="/documents/createform">
                                <Button
                                    type="primary"
                                    htmlType="button"
                                    icon={<PlusOutlined />}
                                    style={{
                                        marginLeft: "10px",
                                        marginRight: "10px",
                                    }}
                                >
                                    Add
                                </Button>
                            </Link>
                            <Button
                                style={{ marginRight: "10px" }}
                                type={
                                    viewMode === "card" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("card")}
                            >
                                <UnorderedListOutlined />
                            </Button>
                            <Button
                                type={
                                    viewMode === "card" ? "default" : "primary"
                                }
                                onClick={() => setViewMode("table")}
                                disabled
                            >
                                <WindowsOutlined />
                            </Button>
                        </div>
                    </div>
                    <div style={{ overflowX: "scroll" }}>
                        {viewMode === "card" ? renderCards() : null}
                        <h4>Total Documents: {dataSource.length}</h4>

                        <Modal
                            title="Confirm Deletion"
                            visible={deleteModalVisible}
                            onOk={handleDelete}
                            onCancel={() => setDeleteModalVisible(false)}
                            okText="Delete"
                            cancelText="Cancel"
                        >
                            <p>
                                Are you sure you want to delete this document?
                            </p>
                        </Modal>
                    </div>
                </Space>
            </Col>
        </Row>
    );
};

export default Documents;
