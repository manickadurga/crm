--
-- PostgreSQL database dump
--

-- Dumped from database version 16.2 (Debian 16.2-1.pgdg120+2)
-- Dumped by pg_dump version 16.2 (Ubuntu 16.2-1.pgdg20.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: jo_blocks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jo_blocks (
    blockid integer NOT NULL,
    tabid integer NOT NULL,
    blocklabel character varying(100) NOT NULL,
    sequence integer,
    show_title integer,
    visible integer DEFAULT 0 NOT NULL,
    create_view integer DEFAULT 0 NOT NULL,
    edit_view integer DEFAULT 0 NOT NULL,
    detail_view integer DEFAULT 0 NOT NULL,
    display_status integer DEFAULT 1 NOT NULL,
    iscustom integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    orgid integer
);


ALTER TABLE public.jo_blocks OWNER TO postgres;

--
-- Name: jo_blocks_blockid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jo_blocks_blockid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jo_blocks_blockid_seq OWNER TO postgres;

--
-- Name: jo_blocks_blockid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jo_blocks_blockid_seq OWNED BY public.jo_blocks.blockid;


--
-- Name: jo_fields; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jo_fields (
    fieldid integer NOT NULL,
    tabid integer NOT NULL,
    columnname character varying(30) NOT NULL,
    tablename character varying(100),
    generatedtype integer DEFAULT 0 NOT NULL,
    uitype character varying(30) NOT NULL,
    fieldname character varying(50) NOT NULL,
    fieldlabel character varying(50) NOT NULL,
    readonly smallint NOT NULL,
    presence integer DEFAULT 1 NOT NULL,
    defaultvalue text,
    maximumlength integer,
    sequence integer,
    block integer,
    displaytype integer,
    typeofdata character varying(100),
    quickcreate integer DEFAULT 1 NOT NULL,
    quickcreatesequence integer,
    info_type character varying(20),
    masseditable integer DEFAULT 1 NOT NULL,
    helpinfo text,
    summaryfield integer DEFAULT 0 NOT NULL,
    headerfield smallint DEFAULT '0'::smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    orgid integer
);


ALTER TABLE public.jo_fields OWNER TO postgres;

--
-- Name: jo_fields_fieldid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jo_fields_fieldid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jo_fields_fieldid_seq OWNER TO postgres;

--
-- Name: jo_fields_fieldid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jo_fields_fieldid_seq OWNED BY public.jo_fields.fieldid;


--
-- Name: jo_parenttabrel; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jo_parenttabrel (
    id bigint NOT NULL,
    parenttabid integer NOT NULL,
    tabid integer NOT NULL,
    sequence integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    orgid integer
);


ALTER TABLE public.jo_parenttabrel OWNER TO postgres;

--
-- Name: jo_parenttabrel_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jo_parenttabrel_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jo_parenttabrel_id_seq OWNER TO postgres;

--
-- Name: jo_parenttabrel_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jo_parenttabrel_id_seq OWNED BY public.jo_parenttabrel.id;


--
-- Name: jo_relatedlists; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jo_relatedlists (
    relationid integer NOT NULL,
    tabid integer,
    related_tabid integer,
    name character varying(100),
    sequence integer,
    label character varying(100),
    presence integer DEFAULT 0 NOT NULL,
    actions character varying(50) DEFAULT ''::character varying NOT NULL,
    relationfieldid integer,
    source character varying(25),
    relationtype character varying(10),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    orgid integer
);


ALTER TABLE public.jo_relatedlists OWNER TO postgres;

--
-- Name: jo_relatedlists_relationid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jo_relatedlists_relationid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jo_relatedlists_relationid_seq OWNER TO postgres;

--
-- Name: jo_relatedlists_relationid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jo_relatedlists_relationid_seq OWNED BY public.jo_relatedlists.relationid;


--
-- Name: jo_tabs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jo_tabs (
    tabid integer NOT NULL,
    name character varying(25) NOT NULL,
    presence integer DEFAULT 1 NOT NULL,
    tabsequence integer,
    tablabel character varying(100),
    modifiedby integer,
    modifiedtime timestamp(0) without time zone,
    customized integer,
    ownedby integer,
    isentitytype integer DEFAULT 1 NOT NULL,
    trial integer DEFAULT 0 NOT NULL,
    version character varying(10),
    parent character varying(30),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    orgid integer
);


ALTER TABLE public.jo_tabs OWNER TO postgres;

--
-- Name: jo_tabs_tabid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jo_tabs_tabid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jo_tabs_tabid_seq OWNER TO postgres;

--
-- Name: jo_tabs_tabid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jo_tabs_tabid_seq OWNED BY public.jo_tabs.tabid;


--
-- Name: jo_blocks blockid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_blocks ALTER COLUMN blockid SET DEFAULT nextval('public.jo_blocks_blockid_seq'::regclass);


--
-- Name: jo_fields fieldid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_fields ALTER COLUMN fieldid SET DEFAULT nextval('public.jo_fields_fieldid_seq'::regclass);


--
-- Name: jo_parenttabrel id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_parenttabrel ALTER COLUMN id SET DEFAULT nextval('public.jo_parenttabrel_id_seq'::regclass);


--
-- Name: jo_relatedlists relationid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_relatedlists ALTER COLUMN relationid SET DEFAULT nextval('public.jo_relatedlists_relationid_seq'::regclass);


--
-- Name: jo_tabs tabid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_tabs ALTER COLUMN tabid SET DEFAULT nextval('public.jo_tabs_tabid_seq'::regclass);


--
-- Data for Name: jo_blocks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jo_blocks (blockid, tabid, blocklabel, sequence, show_title, visible, create_view, edit_view, detail_view, display_status, iscustom, created_at, updated_at, orgid) FROM stdin;
1	1	Contact Information	1	0	0	0	0	0	1	0	\N	\N	\N
2	1	Address Information	2	0	0	0	0	0	1	0	\N	\N	\N
3	1	Additional Information	3	0	0	0	0	0	1	0	\N	\N	\N
4	2	Tasks	1	0	0	0	0	0	1	0	\N	\N	\N
5	3	Add Tasks	1	0	0	0	0	0	1	0	\N	\N	\N
6	5	WorkOrder Signature	1	1	1	0	0	0	1	0	2024-06-01 04:18:24	2024-06-01 04:18:24	\N
7	6	WorkOrder Signature	1	1	1	0	0	0	1	0	2024-06-01 04:23:53	2024-06-01 04:23:53	\N
\.


--
-- Data for Name: jo_fields; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jo_fields (fieldid, tabid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, info_type, masseditable, helpinfo, summaryfield, headerfield, created_at, updated_at, orgid) FROM stdin;
28	2	duedate	jo_teamtasks	1	23	duedate	Duedate	1	2	\N	100	9	4	1	D~O	1	0	BAS	1	\N	0	0	\N	\N	\N
29	2	estimate	jo_teamtasks	1	70	estimate	Estimate	1	2	\N	100	10	4	1	D~O	1	0	BAS	1	\N	0	0	\N	\N	\N
30	2	description	jo_teamtasks	1	1	description	Description	1	2	\N	100	11	4	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
31	3	tasknumber	jo_tasks	1	7	tasknumber	Tasknumber	1	2	\N	100	1	5	1	I~M	1	0	BAS	1	\N	0	0	\N	\N	\N
32	3	projects	jo_tasks	1	52	projects	Projects	1	2	\N	100	2	5	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
1	1	image	jo_customers	1	69	image	Image	1	2	\N	100	1	1	1	V~O	3	\N	ADV	0	\N	0	0	\N	\N	\N
2	1	name	jo_customers	1	2	name	Name	1	2	\N	100	2	1	1	V~M	1	0	BAS	0	\N	0	0	\N	\N	\N
3	1	primary_email	jo_customers	1	13	primary_email	Primary Email	1	2	\N	100	3	1	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
4	1	primary_phone	jo_customers	1	57	primary_phone	Primary Phone	1	2	\N	100	4	1	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
5	1	website	jo_customers	1	17	website	Website	1	2	\N	100	5	1	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
6	1	fax	jo_customers	1	1	fax	Fax	1	2	\N	100	6	1	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
7	1	fiscal_information	jo_customers	1	1	fiscal_information	Fiscal Information	1	2	\N	100	7	1	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
8	1	projects	jo_customers	1	33	projects	Projects	1	2	\N	100	8	1	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
9	1	contact_type	jo_customers	1	16	contact_type	Contact Type	1	2	\N	100	9	1	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
10	1	tags	jo_customers	1	33	tags	Tags	1	2	\N	100	10	1	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
11	1	find_address	jo_customers	1	24	find_address	Find Address	1	2	\N	100	1	2	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
12	1	country	jo_customers	1	16	country	Country	1	2	\N	100	2	2	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
13	1	city	jo_customers	1	1	city	City	1	2	\N	100	3	2	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
14	1	postal_code	jo_customers	1	1	postal_code	Postal Code	1	2	\N	100	4	2	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
15	1	address	jo_customers	1	24	address	Address	1	2	\N	100	5	2	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
16	1	address_2	jo_customers	1	24	address_2	Address 2	1	2	\N	100	6	2	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
17	1	coordinates	jo_customers	1	56	coordinates	Coordinates	1	2	\N	100	7	2	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
18	1	type	jo_customers	0	16	type	Type	1	2	\N	100	1	3	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
19	1	type_suffix	jo_customers	1	16	type_suffix	NULL	1	2	\N	100	2	3	1	V~O	1	0	BAS	0	\N	0	0	\N	\N	\N
20	2	tasknumber	jo_teamtasks	1	7	tasknumber	Tasknumber	1	2	\N	100	1	4	1	I~M	1	0	BAS	1	\N	0	0	\N	\N	\N
21	2	projects	jo_teamtasks\n	1	52	projects	Projects	1	2	\N	100	2	4	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
22	2	status	jo_teamtasks	1	52	status	Status	1	2	\N	100	3	4	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
33	3	status	jo_tasks	1	52	status	Status	1	2	\N	100	3	5	1	v~O	1	0	BAS	1	\N	0	0	\N	\N	\N
23	2	teams	jo_teamtasks	1	52	teams	Teams	1	2	\N	100	4	4	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
25	2	priority	jo_teamtasks	1	52	priority	Priority	1	2	\N	100	6	4	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
26	2	size	jo_teamtasks	1	52	size	Size	1	2	\N	100	7	4	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
27	2	tags	jo_teamtasks	1	52	tags	Tags	1	2	\N	100	8	4	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
34	3	choose	jo_tasks	1	16	choose	Choose Any	1	2	\N	100	4	5	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
35	3	addorremoveemployee	jo_tasks	1	1	addorremoveemployee	Add or Remove	1	2	\N	100	5	5	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
24	2	title	jo_teamtasks	1	2	title	Title	1	2	\N	100	5	4	1	V~M	1	0	BAS	1	\N	0	0	\N	\N	\N
36	3	title	jo_tasks	1	1	title	Title	1	2	\N	100	6	5	1	V~M	1	0	BAS	1	\N	0	0	\N	\N	\N
37	3	priority	jo_tasks	1	52	priority	Priority	1	2	\N	100	7	5	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
38	3	size	jo_tasks	1	52	size	Size	1	2	\N	100	8	5	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
39	3	tags	jo_tasks	1	52	tags	Tags	1	2	\N	100	9	5	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
40	3	duedate	jo_tasks	1	23	duedate	Due date	1	2	\N	100	10	5	1	D~O	1	0	BAS	1	\N	0	0	\N	\N	\N
41	3	estimate	jo_tasks	1	17	estimate	Estimate	1	2	\N	100	11	5	1	D~O	1	0	BAS	1	\N	0	0	\N	\N	\N
42	3	description	jo_tasks	1	1	description	Description	1	2	\N	100	12	5	1	V~O	1	0	BAS	1	\N	0	0	\N	\N	\N
43	6	first_name	jo_purchases	0		first_name	First Name	0	1	\N	\N	\N	7	\N	\N	1	\N	\N	1	\N	0	0	2024-06-01 04:23:53	2024-06-01 04:23:53	\N
\.


--
-- Data for Name: jo_parenttabrel; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jo_parenttabrel (id, parenttabid, tabid, sequence, created_at, updated_at, orgid) FROM stdin;
1	2	6	2	2024-06-01 04:23:53	2024-06-01 04:23:53	\N
\.


--
-- Data for Name: jo_relatedlists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jo_relatedlists (relationid, tabid, related_tabid, name, sequence, label, presence, actions, relationfieldid, source, relationtype, created_at, updated_at, orgid) FROM stdin;
1	6	3	get_related_list	1	Tasks	0	ADD	0	\N	\N	2024-06-01 04:23:53	2024-06-01 04:23:53	\N
\.


--
-- Data for Name: jo_tabs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jo_tabs (tabid, name, presence, tabsequence, tablabel, modifiedby, modifiedtime, customized, ownedby, isentitytype, trial, version, parent, created_at, updated_at, orgid) FROM stdin;
1	Customers	1	1	Customers	\N	\N	0	0	1	0	\N	Sales	\N	\N	\N
2	Teamtask	1	1	Teamtask	\N	\N	0	0	1	0	\N	Sales	\N	\N	\N
3	Tasks	1	1	Tasks	\N	\N	0	0	1	0	\N	Sales	\N	\N	\N
4	Reach	0	-1	Reach	\N	2024-06-01 04:07:46	0	0	1	0	2	Tasks	2024-06-01 04:07:46	2024-06-01 04:07:46	\N
5	Report	0	-1	Report	\N	2024-06-01 04:18:24	0	0	1	0	2	Tasks	2024-06-01 04:18:24	2024-06-01 04:18:24	\N
6	Purchase	0	-1	Purchase	\N	2024-06-01 04:23:53	0	0	1	0	2	Sales	2024-06-01 04:23:53	2024-06-01 04:23:53	\N
\.


--
-- Name: jo_blocks_blockid_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jo_blocks_blockid_seq', 1, true);


--
-- Name: jo_fields_fieldid_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jo_fields_fieldid_seq', 1, false);


--
-- Name: jo_parenttabrel_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jo_parenttabrel_id_seq', 1, true);


--
-- Name: jo_relatedlists_relationid_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jo_relatedlists_relationid_seq', 1, true);


--
-- Name: jo_tabs_tabid_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jo_tabs_tabid_seq', 3, true);


--
-- Name: jo_blocks jo_blocks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_blocks
    ADD CONSTRAINT jo_blocks_pkey PRIMARY KEY (blockid);


--
-- Name: jo_fields jo_fields_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_fields
    ADD CONSTRAINT jo_fields_pkey PRIMARY KEY (fieldid);


--
-- Name: jo_parenttabrel jo_parenttabrel_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_parenttabrel
    ADD CONSTRAINT jo_parenttabrel_pkey PRIMARY KEY (id);


--
-- Name: jo_relatedlists jo_relatedlists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_relatedlists
    ADD CONSTRAINT jo_relatedlists_pkey PRIMARY KEY (relationid);


--
-- Name: jo_tabs jo_tabs_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_tabs
    ADD CONSTRAINT jo_tabs_name_unique UNIQUE (name);


--
-- Name: jo_tabs jo_tabs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_tabs
    ADD CONSTRAINT jo_tabs_pkey PRIMARY KEY (tabid);


--
-- Name: jo_fields_block_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_fields_block_index ON public.jo_fields USING btree (block);


--
-- Name: jo_fields_displaytype_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_fields_displaytype_index ON public.jo_fields USING btree (displaytype);


--
-- Name: jo_fields_fieldname_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_fields_fieldname_index ON public.jo_fields USING btree (fieldname);


--
-- Name: jo_fields_tabid_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_fields_tabid_index ON public.jo_fields USING btree (tabid);


--
-- Name: jo_relatedlists_related_tabid_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_relatedlists_related_tabid_index ON public.jo_relatedlists USING btree (related_tabid);


--
-- Name: jo_relatedlists_relationfieldid_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_relatedlists_relationfieldid_index ON public.jo_relatedlists USING btree (relationfieldid);


--
-- Name: jo_relatedlists_tabid_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_relatedlists_tabid_index ON public.jo_relatedlists USING btree (tabid);


--
-- Name: jo_tabs_modifiedby_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jo_tabs_modifiedby_index ON public.jo_tabs USING btree (modifiedby);


--
-- Name: jo_blocks fk_orgid; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_blocks
    ADD CONSTRAINT fk_orgid FOREIGN KEY (orgid) REFERENCES public.jo_organizations(organizationid);


--
-- Name: jo_tabs fk_orgid; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_tabs
    ADD CONSTRAINT fk_orgid FOREIGN KEY (orgid) REFERENCES public.jo_organizations(organizationid);


--
-- Name: jo_fields fk_orgid; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_fields
    ADD CONSTRAINT fk_orgid FOREIGN KEY (orgid) REFERENCES public.jo_organizations(organizationid);


--
-- Name: jo_relatedlists fk_orgid; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_relatedlists
    ADD CONSTRAINT fk_orgid FOREIGN KEY (orgid) REFERENCES public.jo_organizations(organizationid);


--
-- Name: jo_parenttabrel fk_orgid; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_parenttabrel
    ADD CONSTRAINT fk_orgid FOREIGN KEY (orgid) REFERENCES public.jo_organizations(organizationid);


--
-- Name: jo_blocks jo_blocks_tabid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_blocks
    ADD CONSTRAINT jo_blocks_tabid_foreign FOREIGN KEY (tabid) REFERENCES public.jo_tabs(tabid) ON DELETE CASCADE;


--
-- Name: jo_fields jo_fields_block_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_fields
    ADD CONSTRAINT jo_fields_block_foreign FOREIGN KEY (block) REFERENCES public.jo_blocks(blockid) ON DELETE SET NULL;


--
-- Name: jo_fields jo_fields_tabid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jo_fields
    ADD CONSTRAINT jo_fields_tabid_foreign FOREIGN KEY (tabid) REFERENCES public.jo_tabs(tabid) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

