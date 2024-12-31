import React, { useState, useEffect } from "react";
import {
    Space,
    Table,
    Input,
    Button,
    Modal,
    message,
    Card,
    Row,
    Col,
    List,
} from "antd";
import {
    SearchOutlined,
    EyeOutlined,
    UnorderedListOutlined,
    WindowsOutlined,
    DeleteOutlined,
    EditOutlined,
    ArrowLeftOutlined,
    PlusOutlined,
} from "@ant-design/icons";
import { getDataFunction } from "../../../API/index";
import { Link } from "react-router-dom";

const Tags = () => {
    const [searchText, setSearchText] = useState({});
    const [dataSource, setDataSource] = useState([]);
    const [columns, setColumns] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalRecords, setTotalRecords] = useState(0);
    const [deleteModalVisible, setDeleteModalVisible] = useState(false);
    const [selectedTag, setSelectedTag] = useState(null);
    const [viewMode, setViewMode] = useState("table");
    const [selectedTagType, setSelectedTagType] = useState(null);
    const [selectedTagValues, setSelectedTagValues] = useState([]);

    const tagFields = [
        { type: "All" },
        { type: "Income", values: ["completed", "Third party API"] },
        { type: "Task", values: ["process", "Incompleted", "Todo"] },
        { type: "Invoice", values: ["completed"] },
        { type: "OrganizationContact" },
        { type: "proposals" },
        { type: "Equipment" },
        { type: "payment" },
    ];

    useEffect(() => {
        fetchTag(currentPage, selectedTagType);
    }, [currentPage, selectedTagType, searchText]);

    const fetchTag = (page, tagType) => {
        setLoading(true);
        getDataFunction("tags",page)
            .then((res) => {
                let sortedTag = res.Data;
                if (tagType) {
                    sortedTag = sortedTag.filter((tag) => tag.type === tagType);
                }
                const filteredTag = sortedTag.filter((tag) =>
                    Object.keys(searchText).every((key) => {
                        const tagValue = key.includes(".")
                            ? key
                                  .split(".")
                                  .reduce((obj, k) => (obj || {})[k], tag)
                            : tag[key];
                        return (
                            tagValue &&
                            tagValue
                                .toString()
                                .toLowerCase()
                                .includes(searchText[key].toLowerCase())
                        );
                    })
                );

                setDataSource(filteredTag);
                setColumns(
                    Object.keys(sortedTag[0] || {}).map((key) => ({
                        title: (
                            <div>
                                {key}
                                <Input
                                    placeholder={`Search ${key}`}
                                    value={searchText[key] || ""}
                                    onChange={(e) =>
                                        handleSearch(e.target.value, key)
                                    }
                                    style={{ marginTop: 8, display: "block" }}
                                />
                            </div>
                        ),
                        dataIndex: key,
                        key: key,
                        render: (text) => text,
                    }))
                );
                setTotalRecords(filteredTag.length);
                setLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching tags:", error);
                setLoading(false);
            });
    };

    const handleSearch = (value, key) => {
        setSearchText((prevSearchText) => {
            const updatedSearchText = { ...prevSearchText };
            if (value === "") {
                delete updatedSearchText[key];
            } else {
                updatedSearchText[key] = value;
            }
            return updatedSearchText;
        });
    };

    const handlePageChange = (page) => {
        setCurrentPage(page);
    };

    const showDeleteModal = () => {
        setDeleteModalVisible(true);
    };

    const handleDelete = () => {
        // Call delete API here, then refresh the list
        message.success("Tag deleted successfully!");
        fetchTag(currentPage, selectedTagType);
        setDeleteModalVisible(false);
        setSelectedTag(null);
    };

    const onRowClick = (record) => {
        setSelectedTag((prevSelectedTag) =>
            prevSelectedTag && prevSelectedTag.id === record.id ? null : record
        );
    };

    const renderCards = () => (
        <Row gutter={16}>
            {dataSource.map((tag) => (
                <Col key={tag.id} span={6}>
                    <Card
                        title={tag.Name}
                        onClick={() => onRowClick(tag)}
                        style={{
                            cursor: "pointer",
                            backgroundColor:
                                selectedTag && selectedTag.id === tag.id
                                    ? "#f0f0f0"
                                    : "white",
                        }}
                    >
                        {Object.keys(tag).map((key) => (
                            <p key={key}>
                                <strong>{key}:</strong> {tag[key]}
                            </p>
                        ))}
                    </Card>
                </Col>
            ))}
        </Row>
    );

    const renderColumns = () =>
        columns.map((column) => ({
            ...column,
            onCell: (record) => ({
                style: {
                    backgroundColor:
                        selectedTag && selectedTag.id === record.id
                            ? "#f0f0f0"
                            : "white",
                    cursor: "pointer",
                },
            }),
        }));

    const handleTagTypeClick = (item) => {
        setSelectedTagType(item.type);
        setSelectedTagValues(item.values || []);
        setSearchText({});
    };

    return (
        <Row gutter={16}>
            <Col span={6}>
                <Card
                    bordered={false}
                    extra={
                        <Button
                            type="text"
                            icon={<SearchOutlined />}
                            onClick={() => console.log("Search button clicked")}
                        >
                            Search
                        </Button>
                    }
                >
                    <List
                        itemLayout="horizontal"
                        dataSource={tagFields}
                        renderItem={(item) => (
                            <List.Item
                                style={{
                                    borderRadius: "50%",
                                    padding: "4px 12px",
                                    margin: "2px",
                                    border: "4px solid #d9d9d9",
                                    backgroundColor:
                                        selectedTagType === item.type
                                            ? "#f0f0f0"
                                            : "white",
                                    cursor: "pointer",
                                    display: "inline-flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                    width: "auto",
                                }}
                                onClick={() => handleTagTypeClick(item)}
                            >
                                <span
                                    style={{
                                        fontSize: "12px",
                                        fontWeight: "bold",
                                    }}
                                >
                                    {item.type}
                                </span>
                            </List.Item>
                        )}
                    />
                </Card>
            </Col>
            <Col span={18}>
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
                            <Link to="/tags">
                                <Button
                                    shape="circle"
                                    htmlType="button"
                                    size="small"
                                >
                                    <ArrowLeftOutlined />
                                </Button>
                            </Link>
                            <b style={{ fontSize: "18px", marginLeft: "18px" }}>
                                Tags
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
                            {selectedTag && (
                                <div style={{ gap: "2px" }}>
                                    <Link to={`/tags/view/${selectedTag.id}`}>
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
                                    <Link to={`/tags/edit/${selectedTag.id}`}>
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

                            <Link to="/tags/createform">
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
                                    viewMode === "table" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("table")}
                            >
                                <WindowsOutlined />
                            </Button>
                            <Button
                                type={
                                    viewMode === "card" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("card")}
                            >
                                <UnorderedListOutlined />
                            </Button>
                        </div>
                    </div>
                    <div style={{ overflowX: "scroll" }}>
                        {viewMode === "table" ? (
                            <>
                                <Table
                                    className="datatable customers-table"
                                    loading={loading}
                                    columns={renderColumns()}
                                    dataSource={dataSource}
                                    pagination={{
                                        current: currentPage,
                                        pageSize: 10,
                                        total: totalRecords,
                                        onChange: handlePageChange,
                                    }}
                                    onRow={(record) => ({
                                        onClick: () => onRowClick(record),
                                        style: {
                                            backgroundColor:
                                                selectedTag &&
                                                selectedTag.id === record.id
                                                    ? "#f0f0f0"
                                                    : "white",
                                            cursor: "pointer",
                                        },
                                    })}
                                />
                            </>
                        ) : (
                            renderCards()
                        )}
                        <h4>Total Tags: {totalRecords}</h4>

                        <Modal
                            title="Delete Confirmation"
                            open={deleteModalVisible}
                            onCancel={() => setDeleteModalVisible(false)}
                            onOk={handleDelete}
                            okText="Delete"
                            cancelText="Cancel"
                        >
                            Are you sure you want to delete the selected item?
                        </Modal>
                    </div>
                </Space>
            </Col>
        </Row>
    );
};

export default Tags;
