import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Form, Input, Button, message, Select } from "antd";
import { getTeamTaskById, updateTeamTask } from "../../API";

const { Option } = Select;

const UpdateTeamTask = ({ onClose, teams = [] }) => {
  const { id } = useParams();
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const fetchTask = async () => {
      try {
        setLoading(true);
        const task = await getTeamTaskById(id);
        if (task) {
          form.setFieldsValue({
            tasksnumber: task.tasksnumber,
            projects: task.projects,
            status: task.status,
            teams: task.teams,
            title: task.title,
            priority: task.priority,
            size: task.size,
            tags: task.tags,
            duedate: task.duedate,
            estimate_days: task.estimate_days,
            estimate_hours: task.estimate_hours,
            estimate_minutes: task.estimate_minutes,
          });
        }
        setLoading(false);
      } catch (error) {
        console.error("Error fetching team task:", error);
        message.error("Failed to load task data");
        setLoading(false);
      }
    };
    if (id) {
      fetchTask();
    }
  }, [id, form]);

  const onFinish = async (values) => {
    try {
      setLoading(true);
      await updateTeamTask(id, values);
      message.success("Task updated successfully");
      setLoading(false);
      onClose();
    } catch (error) {
      console.error("Error updating team task:", error);
      message.error("Failed to update task");
      setLoading(false);
    }
  };

  return (
    <div>
      <h3>Update Form</h3>
      <Form form={form} layout="vertical" onFinish={onFinish}>
        <Form.Item
          name="tasksnumber"
          label="Task Number"
          rules={[{ required: true, message: "Please input the task number!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="projects"
          label="Projects"
          rules={[{ message: "Please select a Project!" }]}
        >
          <Select placeholder="Select a Project">
            <Option value="none">None</Option>
            <Option value="Project1">Project 1</Option>
            <Option value="Project2">Project 2</Option>
            <Option value="Project3">Project 3</Option>
          </Select>
        </Form.Item>
        <Form.Item
          name="status"
          label="Status"
          rules={[{ message: "Please select a Status!" }]}
        >
          <Select placeholder="Select a Status">
            <Option value="none">None</Option>
            <Option value="open">Open</Option>
            <Option value="inprogress">In Progress</Option>
            <Option value="inreview">In Review</Option>
            <Option value="completed">Completed</Option>
            <Option value="closed">Closed</Option>
          </Select>
        </Form.Item>
        <Form.Item
          name="teams"
          label="Teams"
          rules={[{ message: "Please select a team!" }]}
        >
          <Select placeholder="Select a team">
            <Option value="none">None</Option>
            <Option value="Employees">Employees</Option>
            <Option value="Contracters">Contracters</Option>
            <Option value="Designers">Designers</Option>
            <Option value="QA">QA</Option>
            <Option value="Default Team">Default Team</Option>
          </Select>
        </Form.Item>
        <Form.Item
          name="title"
          label="Title"
          rules={[{ required: true, message: "Please input the title!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="priority"
          label="Priority"
          rules={[{ message: "Please select a priority!" }]}
        >
          <Select placeholder="Select a priority">
            <Option value="none">None</Option>
            <Option value="low">Low</Option>
            <Option value="Medium">Medium</Option>
            <Option value="high">High</Option>
            <Option value="urgent">Urgent</Option>
          </Select>
        </Form.Item>
        <Form.Item
          name="size"
          label="Size"
          rules={[{ message: "Please select a size!" }]}
        >
          <Select placeholder="Select a size">
            <Option value="none">None</Option>
            <Option value="large">Large</Option>
            <Option value="Medium">Medium</Option>
            <Option value="small">Small</Option>
            <Option value="tiny">Tiny</Option>
          </Select>
        </Form.Item>
        <Form.Item
          name="tags"
          label="Tags"
          // rules={[{ message: "Please select Tags!" }]}
        >
          <Select mode="tags" placeholder="Select or type tags">
            <Option value="none">None</Option>
            <Option value="VIP">VIP</Option>
            <Option value="urgent">Urgent</Option>
            <Option value="crazy">Crazy</Option>
            <Option value="Broken">Broken</Option>
            <Option value="TODO">TODO</Option>
            <Option value="In Progress">In Progress</Option>
            <Option value="Verified">Verified</Option>
            <Option value="Third Party API">Third Party API</Option>
            <Option value="Killer">Killer</Option>
            <Option value="Idiot">Idiot</Option>
            <Option value="super">Super</Option>
            <Option value="WIP">WIP</Option>
            <Option value="type:help wanted">Help Wanted</Option>
            <Option value="type:question">Question</Option>
            <Option value="bug">Bug</Option>
            <Option value="priority:highest">Priority: Highest</Option>
            <Option value="enhancement">Enhancement</Option>
            <Option value="type:enhancement">Type: Enhancement</Option>
            <Option value="Desktop:timer">Desktop: Timer</Option>
            <Option value="changes requested">Changes Requested</Option>
            <Option value="type:bug">Type: Bug</Option>
            <Option value="fix">Fix</Option>
            <Option value="ui">UI</Option>
            <Option value="priority:low">Priority: Low</Option>
            <Option value="type:devops">DevOps</Option>
            <Option value="type:performance">Performance</Option>
          </Select>
        </Form.Item>
        <Form.Item
          name="duedate"
          label="Due Date"
          rules={[{ message: "Please select your Due Date!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="estimate_days"
          label="Estimate Days"
          rules={[{ message: "Please select estimate days!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="estimate_hours"
          label="Estimate Hours"
          rules={[{ required: true, message: "Please input estimate hours!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item
          name="estimate_minutes"
          label="Estimate Minutes"
          rules={[{ required: true, message: "Please input estimate minutes!" }]}
        >
          <Input />
        </Form.Item>
        <Form.Item>
          <Button type="primary" htmlType="submit" loading={loading}>
            Update Task
          </Button>
          <Button onClick={onClose} style={{ marginLeft: 8 }}>
            Cancel
          </Button>
        </Form.Item>
      </Form>
    </div>
  );
};

export default UpdateTeamTask;
